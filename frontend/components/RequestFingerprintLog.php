<?php

namespace frontend\components;

use Yii;
use yii\web\Request;
use yii\web\Response;

/**
 * Short-lived diagnostic log of request "fingerprints" for page views.
 *
 * Purpose: find out how the bot traffic that floods GA4 (Chrome, Windows 10,
 * 1280x1200 screen, ~1 page per "visit") differs from people before the page
 * is rendered: source IP ranges, missing or inconsistent browser headers,
 * cookies. MyDevil's access log does not record request headers.
 *
 * Only HTML page views are recorded (GET/HEAD, not AJAX, HTML response).
 * One JSON line per request in @runtime/fingerprints/fp-YYYYMMDD.jsonl.
 * Files older than two days are deleted, and logging stops by itself after
 * params['fingerprintLog.until'] (empty = off). Summarise with
 * `php yii fingerprint/report`.
 */
final class RequestFingerprintLog
{
    private const KEEP_SECONDS = 2 * 86400;

    public static function record(Response $response): void
    {
        try {
            if (!self::enabled()) {
                return;
            }
            $request = Yii::$app->request;
            if (!$request instanceof Request
                || !in_array($request->method, ['GET', 'HEAD'], true)
                || $request->isAjax
                || $response->format !== Response::FORMAT_HTML
            ) {
                return;
            }

            $h = $request->headers;
            $cut = static fn(?string $v, int $len): string => mb_substr((string)$v, 0, $len);
            $referer = (string)$h->get('Referer', '');

            $line = [
                't'     => gmdate('c'),
                'ip'    => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
                'xff'   => $cut($h->get('X-Forwarded-For'), 100),
                'xri'   => $cut($h->get('X-Real-IP'), 45),
                'm'     => $request->method,
                'p'     => $cut($request->url, 200),
                's'     => $response->statusCode,
                'proto' => (string)($_SERVER['SERVER_PROTOCOL'] ?? ''),
                'ua'    => $cut($request->userAgent, 300),
                'al'    => $cut($h->get('Accept-Language'), 80),
                'acc'   => $cut($h->get('Accept'), 80),
                'ae'    => $cut($h->get('Accept-Encoding'), 40),
                'chua'  => $cut($h->get('Sec-CH-UA'), 150),
                'chm'   => $cut($h->get('Sec-CH-UA-Mobile'), 5),
                'chp'   => $cut($h->get('Sec-CH-UA-Platform'), 30),
                'sfs'   => $cut($h->get('Sec-Fetch-Site'), 20),
                'sfm'   => $cut($h->get('Sec-Fetch-Mode'), 20),
                'sfd'   => $cut($h->get('Sec-Fetch-Dest'), 20),
                'sfu'   => $cut($h->get('Sec-Fetch-User'), 5),
                'uir'   => $cut($h->get('Upgrade-Insecure-Requests'), 5),
                'dnt'   => $cut($h->get('DNT'), 5),
                'ref'   => $referer !== '' ? $cut((string)parse_url($referer, PHP_URL_HOST), 80) : '',
                'ck'    => array_slice(array_keys($_COOKIE), 0, 12),
                'bg'    => BotGuard::isAutomated(),
            ];

            $dir = Yii::getAlias('@runtime/fingerprints');
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                return;
            }
            @file_put_contents(
                $dir . '/fp-' . gmdate('Ymd') . '.jsonl',
                json_encode($line, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND | LOCK_EX
            );

            if (random_int(1, 200) === 1) {
                self::prune($dir);
            }
        } catch (\Throwable) {
            // Diagnostics must never break a page.
        }
    }

    private static function enabled(): bool
    {
        $until = (string)(Yii::$app->params['fingerprintLog.until'] ?? '');
        if ($until === '') {
            return false;
        }
        $ts = strtotime($until);

        return $ts !== false && time() < $ts;
    }

    private static function prune(string $dir): void
    {
        foreach (glob($dir . '/fp-*.jsonl') ?: [] as $file) {
            if (filemtime($file) < time() - self::KEEP_SECONDS) {
                @unlink($file);
            }
        }
    }
}
