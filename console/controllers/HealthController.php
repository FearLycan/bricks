<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\Set;
use Throwable;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;

/**
 * Hourly health check (console/config/schedule.php). Looks for quiet failures that never
 * show up as a broken page: Brickset data not updating, an old sitemap, stale BotGuard
 * ranges, a wave of logged errors, a schedule that stopped running. Emails one summary to
 * params['alerts.email']. Each problem alerts at most once per 12 h, and an "all clear"
 * mail follows when a reported problem goes away. Ported from store-base.
 *
 *   php yii health/check            run the checks (mails only on change)
 *   php yii health/check --print=1  print the findings, send nothing
 */
final class HealthController extends Controller
{
    public bool $print = false;

    private const REPEAT_AFTER = 12 * 3600;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['print']);
    }

    public function actionCheck(): int
    {
        $found = [];
        foreach (['brickset', 'sitemap', 'botRanges', 'errors', 'schedule'] as $check) {
            try {
                foreach ($this->{$check}() as $key => $text) {
                    $found[$key] = $text;
                }
            } catch (Throwable $e) {
                $found['check.' . $check] = "Check '{$check}' itself failed: " . $e->getMessage();
            }
        }

        if ($this->print) {
            $this->stdout($found ? implode("\n\n", $found) . "\n" : "All good.\n");
            return ExitCode::OK;
        }

        $cache = Yii::$app->cache;
        $known = (array)($cache->get('health.open') ?: []);
        $new = [];
        foreach ($found as $key => $text) {
            if (!isset($known[$key]) || time() - $known[$key] >= self::REPEAT_AFTER) {
                $new[$key] = $text;
                $known[$key] = time();
            }
        }
        $resolved = array_diff_key($known, $found);
        foreach (array_keys($resolved) as $key) {
            unset($known[$key]);
        }
        $cache->set('health.open', $known, 7 * 86400);

        if ($new === [] && $resolved === []) {
            return ExitCode::OK;
        }
        $body = '';
        if ($new) {
            $body .= "Problems found:\n\n" . implode("\n\n", $new) . "\n\n";
        }
        if ($resolved) {
            $body .= 'Resolved since the last alert: ' . implode(', ', array_keys($resolved)) . "\n\n";
        }
        $body .= 'Still open: ' . (count($found) ?: 'none') . ".\nCurrent state: php yii health/check --print=1";
        $this->send($new ? count($new) . ' problem(s)' : 'all clear', $body);

        return ExitCode::OK;
    }

    /** No set updated for 3 days: the Brickset sync is not running or failing. */
    private function brickset(): array
    {
        $last = (new Query())->from(Set::tableName())->max('updated_at');
        $ts = $last ? strtotime((string)$last) : 0;
        if ($ts > time() - 3 * 86400) {
            return [];
        }

        return ['brickset' => 'BRICKSET: no set has been updated since ' . ($ts ? date('Y-m-d H:i', $ts) : 'ever')
            . ". Check console/runtime/logs/schedule-brickset.log (brickset/sync-recently-updated-sets)."];
    }

    private function sitemap(): array
    {
        $file = Yii::getAlias('@frontend/web/sitemap.xml');
        $ts = is_file($file) ? (int)filemtime($file) : 0;
        if ($ts > time() - 2 * 86400) {
            return [];
        }

        return ['sitemap' => 'SITEMAP: sitemap.xml was last generated ' . ($ts ? date('Y-m-d H:i', $ts) : 'never')
            . '. Check console/runtime/logs/schedule-sitemap.log.'];
    }

    private function botRanges(): array
    {
        $files = glob(Yii::getAlias('@console/runtime/botguard/*')) ?: [];
        if ($files === []) {
            return [];
        }
        $ts = max(array_map('filemtime', $files));
        if ($ts > time() - 10 * 86400) {
            return [];
        }

        return ['botRanges' => 'BOTGUARD: data-centre IP ranges were last updated ' . date('Y-m-d', $ts)
            . ' (weekly bot-guard/update-ranges). Bots from new cloud ranges may count as visitors.'];
    }

    /** 20+ errors with the same category in the last hour (log table, DbLogTarget). */
    private function errors(): array
    {
        $rows = (new Query())
            ->select(['category', 'n' => 'COUNT(*)', 'sample' => 'MAX(LEFT(message, 160))'])
            ->from('{{%log}}')
            ->where(['level' => 'error'])
            ->andWhere(['>', 'created_at', date('Y-m-d H:i:s', time() - 3600)])
            ->groupBy('category')
            ->having(['>=', 'COUNT(*)', 20])
            ->orderBy(['n' => SORT_DESC])
            ->limit(5)
            ->all();
        $out = [];
        foreach ($rows as $r) {
            $key = 'errors.' . substr(md5((string)$r['category']), 0, 8);
            $out[$key] = "ERRORS: {$r['n']} errors in the last hour in {$r['category']}, e.g.:\n{$r['sample']}";
        }

        return $out;
    }

    /** A scheduled task that has not started for over twice its usual gap (daily tasks: 2 days). */
    private function schedule(): array
    {
        $out = [];
        foreach ((array)require Yii::getAlias('@console/config/schedule.php') as $name => [$expr]) {
            $last = Yii::getAlias('@console/runtime/schedule-' . $name . '.last');
            if (!is_file($last)) {
                continue; // never ran here yet (fresh install or the crontab line is not set)
            }
            $maxGap = $this->maxGapSeconds((string)$expr);
            $age = time() - (int)filemtime($last);
            if ($age > $maxGap) {
                $out['schedule.' . $name] = sprintf(
                    'SCHEDULE: task %s last started %s ago (expected about every %s). Is the schedule/run crontab line still there?',
                    $name,
                    $this->human($age),
                    $this->human((int)($maxGap / 2))
                );
            }
        }

        return $out;
    }

    /** Twice the longest gap a 5-field expression implies, roughly: hourly 2 h, every N h 2N h, daily 2 d, weekly 14 d. */
    private function maxGapSeconds(string $expr): int
    {
        $f = preg_split('/\s+/', trim($expr));
        if (count($f) !== 5) {
            return 14 * 86400;
        }
        [, $hour, $dom, , $dow] = $f;
        if ($dow !== '*' || $dom !== '*') {
            return 14 * 86400;
        }
        if (preg_match('#^\*/(\d+)$#', $hour, $m)) {
            return 2 * (int)$m[1] * 3600; // every N hours
        }
        if ($hour !== '*') {
            return 2 * 86400;
        }

        return 2 * 3600;
    }

    private function human(int $seconds): string
    {
        return $seconds >= 86400 ? round($seconds / 86400, 1) . ' d' : round($seconds / 3600, 1) . ' h';
    }

    private function send(string $what, string $body): void
    {
        $to = trim((string)(Yii::$app->params['alerts.email'] ?? ''));
        if ($to === '' || str_ends_with($to, '@example.com')) {
            $this->stdout("No alerts.email set; not mailing:\n{$body}\n");
            return;
        }
        $from = (string)(Yii::$app->params['senderEmail'] ?? $to);
        try {
            Yii::$app->mailer->compose()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject('[BrickAtlas] health: ' . $what)
                ->setTextBody($body)
                ->send();
        } catch (Throwable $e) {
            Yii::warning('health/check could not send mail: ' . $e->getMessage(), __METHOD__);
            $this->stderr('Mail failed: ' . $e->getMessage() . "\n");
        }
    }
}
