<?php

declare(strict_types=1);

namespace frontend\components;

use common\models\User;
use Throwable;
use Yii;
use yii\base\Event;
use yii\db\Expression;
use yii\web\Response;

/**
 * Bumps the daily traffic counters. Hooked on the web app's afterRequest, so it sees every
 * catalog page that rendered fine and every 404. Requests BotGuard marks as automated
 * count as crawler hits, not visits; admins are not counted. Best effort: a stats failure
 * never breaks the page. Ported from store-base.
 */
final class TrafficRecorder
{
    public static function onAfterRequest(Event $event): void
    {
        try {
            $app = Yii::$app;
            $request = $app->request;
            $response = $app->response;
            if (!$request instanceof \yii\web\Request || !$request->getIsGet() || $request->getIsAjax()) {
                return;
            }
            if ($response->format !== Response::FORMAT_HTML) {
                return;
            }
            if ($response->statusCode === 404) {
                self::record('notfound', true);
                return;
            }
            if ($response->statusCode !== 200) {
                return;
            }
            $route = $app->controller?->action?->getUniqueId() ?? '';
            $type = TrafficClassifier::pageType($route);
            if ($type !== null) {
                self::record($type);
            }
        } catch (Throwable $e) {
            Yii::warning('Traffic stats skipped: ' . $e->getMessage(), __METHOD__);
        }
    }

    public static function recordNotFound(): void
    {
        try {
            if (Yii::$app->request->getIsGet() && !Yii::$app->request->getIsAjax()) {
                self::record('notfound', true);
            }
        } catch (Throwable $e) {
            Yii::warning('Traffic stats skipped: ' . $e->getMessage(), __METHOD__);
        }
    }

    /** On-site search, page 1 only (paging through results is not another search). */
    public static function recordSearch(string $query, int $results): void
    {
        try {
            $request = Yii::$app->request;
            $query = mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $query)));
            if ($query === '' || (int) $request->get('page', 1) > 1 || !self::isHumanVisitor()) {
                return;
            }
            $query = mb_substr($query, 0, 100);
            self::bump('{{%search_daily}}', [
                'day' => self::today(),
                'query_hash' => md5($query),
                'query' => $query,
                'results' => $results,
            ], ['searches' => 1], ['results' => $results]);
        } catch (Throwable $e) {
            Yii::warning('Search stats skipped: ' . $e->getMessage(), __METHOD__);
        }
    }

    private static function record(string $type, bool $logPath = false): void
    {
        $request = Yii::$app->request;
        if (self::isPrefetch()) {
            return;
        }
        $ua = (string) $request->userAgent;
        $day = self::today();

        $bot = TrafficClassifier::bot($ua) ?? (BotGuard::isAutomated() ? 'other' : null);
        if ($bot !== null) {
            self::bump('{{%traffic_bot_daily}}', ['day' => $day, 'bot' => $bot, 'page_type' => $type], ['hits' => 1]);

            return;
        }
        if (self::isAdmin()) {
            return; // our own visits are not traffic
        }

        $ownHost = (string) parse_url($request->hostInfo, PHP_URL_HOST);
        [$source, $refHost] = TrafficClassifier::source(
            (string) $request->referrer,
            $ownHost,
            (string) $request->get('utm_source', '')
        );
        $entry = $source === 'internal' ? 0 : 1;

        self::bump('{{%traffic_daily}}', [
            'day' => $day,
            'page_type' => $type,
            'lang' => mb_substr((string)Yii::$app->language, 0, 5),
            'source' => $source,
            'device' => TrafficClassifier::device($ua),
        ], ['views' => 1, 'entries' => $entry]);

        // Paths only for landings and 404s: that is what the report lists, and it keeps the table small.
        if ($entry === 1 || $logPath) {
            $path = mb_substr('/' . ltrim((string) parse_url($request->url, PHP_URL_PATH), '/'), 0, 255);
            self::bump('{{%traffic_path_daily}}', [
                'day' => $day,
                'path_hash' => md5($path),
                'path' => $path,
                'page_type' => $type,
            ], ['views' => 1, 'entries' => $entry]);
        }

        if ($entry === 1 && $refHost !== '') {
            self::bump('{{%traffic_referrer_daily}}', [
                'day' => $day,
                'host' => mb_substr($refHost, 0, 100),
                'source' => $source,
            ], ['entries' => 1]);
        }
    }

    private static function isHumanVisitor(): bool
    {
        return TrafficClassifier::bot((string) Yii::$app->request->userAgent) === null
            && !BotGuard::isAutomated()
            && !self::isAdmin()
            && !self::isPrefetch();
    }

    private static function isAdmin(): bool
    {
        $identity = Yii::$app->user->getIdentity(false);

        return $identity instanceof User && $identity->isAdmin();
    }

    /** Browser prefetch/prerender is not a view (yet). */
    private static function isPrefetch(): bool
    {
        $headers = Yii::$app->request->headers;
        $purpose = strtolower((string) ($headers->get('Sec-Purpose') ?? $headers->get('Purpose') ?? $headers->get('X-Moz') ?? ''));

        return str_contains($purpose, 'prefetch') || str_contains($purpose, 'prerender');
    }

    private static function today(): string
    {
        return date('Y-m-d');
    }

    /**
     * Insert the row or add to its counters.
     *
     * @param array<string, mixed> $key      key columns plus any initial non-counter values
     * @param array<string, int>   $counters column => amount to add
     * @param array<string, mixed> $set      plain columns to overwrite on update
     */
    private static function bump(string $table, array $key, array $counters, array $set = []): void
    {
        $update = $set;
        foreach ($counters as $col => $by) {
            $update[$col] = new Expression('[[' . $col . ']] + ' . (int) $by);
        }
        Yii::$app->db->createCommand()->upsert($table, $key + $counters, $update)->execute();
    }
}
