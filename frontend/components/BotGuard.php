<?php

namespace frontend\components;

use common\components\IpRangeSet;
use Yii;
use yii\web\Request;

/**
 * Server-side decision per request: is this a person (load GA + AdSense),
 * an automated visitor (serve the page, no tags), or abuse (block)?
 *
 * Built from a day of request fingerprints (11.2026): ~2/3 of page views were
 * robots, many running real headless Chrome from cloud networks, which flooded
 * GA4 (e.g. "Singapore", 1280x1200) and would count as invalid AdSense traffic.
 *
 * Tags off (reason):
 *   declared    - user agent names a bot/tool (Googlebot, Bingbot, curl, ...)
 *   headers     - browser user agent without Accept-Language and Sec-Fetch-*
 *                 (every current browser sends both on a page navigation)
 *   datacenter  - IP in a cloud/hosting range (bot-guard/update-ranges),
 *                 except iCloud Private Relay egress addresses
 * Blocked:
 *   empty-ua    - no user agent at all (403)
 *   blocked-net - params['botGuard.blockCidrs'] (403)
 *   rate        - more than params['botGuard.rateLimit'] page views per
 *                 5 minutes from one IP that is not a verified search bot (429)
 *
 * A client-side check (navigator.webdriver) in the layout still catches
 * headless browsers that slip through.
 */
final class BotGuard
{
    private const PATTERN = '/HeadlessChrome|PhantomJS|Puppeteer|Playwright|Selenium|python-requests|curl\/|wget\/|Go-http-client|okhttp|scrapy|bot\/|bot;|\+https?:\/\/|crawler|spider|slurp|facebookexternalhit|meta-externalagent|AhrefsBot|SemrushBot|Bytespider|GPTBot|ClaudeBot|CCBot|PetalBot/i';

    /** Search engines exempt from the rate limit once their IP is verified by DNS. */
    private const VERIFIABLE = [
        '/Googlebot|Google-InspectionTool|Mediapartners-Google|AdsBot-Google|APIs-Google/i' => ['.googlebot.com', '.google.com', '.googleusercontent.com'],
        '/bingbot|BingPreview|adidxbot/i'                                                  => ['.search.msn.com'],
        '/Applebot/i'                                                                      => ['.applebot.apple.com'],
        '/DuckDuckBot/i'                                                                   => ['.duckduckgo.com'],
        '/YandexBot/i'                                                                     => ['.yandex.ru', '.yandex.net', '.yandex.com'],
    ];

    private const WINDOW = 300;

    /** @var array{tags: bool, block: ?int, reason: string}|null */
    private static ?array $verdict = null;

    /** True when GA / AdSense must stay off this request. */
    public static function isAutomated(): bool
    {
        return !self::verdict()['tags'];
    }

    public static function reason(): string
    {
        return self::verdict()['reason'];
    }

    /**
     * Called on beforeRequest: answers blocked requests with 403/429 before
     * any controller runs.
     */
    public static function enforce(): void
    {
        $v = self::verdict();
        if ($v['block'] === null) {
            return;
        }
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->statusCode = $v['block'];
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
        $response->headers->set('Cache-Control', 'no-store');
        if ($v['block'] === 429) {
            $response->headers->set('Retry-After', (string)self::WINDOW);
            $response->content = "Too many requests. Please slow down.\n";
        } else {
            $response->content = "Access denied.\n";
        }
        Yii::$app->end();
    }

    /** @return array{tags: bool, block: ?int, reason: string} */
    public static function verdict(): array
    {
        if (self::$verdict !== null) {
            return self::$verdict;
        }
        $request = Yii::$app->request;
        if (!$request instanceof Request) {
            return self::$verdict = ['tags' => false, 'block' => null, 'reason' => 'console'];
        }

        $ua = (string)$request->getUserAgent();
        $ip = (string)($request->getRemoteIP() ?? '');
        $params = Yii::$app->params;

        if ($ua === '') {
            return self::$verdict = ['tags' => false, 'block' => 403, 'reason' => 'empty-ua'];
        }
        foreach ((array)($params['botGuard.blockCidrs'] ?? []) as $cidr) {
            if (self::ipInCidr($ip, (string)$cidr)) {
                return self::$verdict = ['tags' => false, 'block' => 403, 'reason' => 'blocked-net'];
            }
        }

        $declared = preg_match(self::PATTERN, $ua) === 1;
        if (!$request->isAjax && self::overRateLimit($ip, $ua, (int)($params['botGuard.rateLimit'] ?? 60))) {
            return self::$verdict = ['tags' => false, 'block' => 429, 'reason' => 'rate'];
        }
        if ($declared) {
            return self::$verdict = ['tags' => false, 'block' => null, 'reason' => 'declared'];
        }

        $h = $request->headers;
        if (str_starts_with($ua, 'Mozilla/') && !$h->has('Accept-Language') && !$h->has('Sec-Fetch-Mode')) {
            return self::$verdict = ['tags' => false, 'block' => null, 'reason' => 'headers'];
        }

        $dir = Yii::getAlias('@console/runtime/botguard');
        if ((new IpRangeSet($dir . '/datacenter'))->contains($ip)
            && !(new IpRangeSet($dir . '/private-relay'))->contains($ip)) {
            return self::$verdict = ['tags' => false, 'block' => null, 'reason' => 'datacenter'];
        }

        return self::$verdict = ['tags' => true, 'block' => null, 'reason' => 'human'];
    }

    /** Page views per IP in the current 5-minute window (file cache). */
    private static function overRateLimit(string $ip, string $ua, int $limit): bool
    {
        if ($ip === '' || $limit <= 0) {
            return false;
        }
        $cache = Yii::$app->cache;
        $key = 'botguard.rate.' . $ip . '.' . intdiv(time(), self::WINDOW);
        $count = (int)$cache->get($key) + 1;
        $cache->set($key, $count, self::WINDOW * 2);
        if ($count <= $limit) {
            return false;
        }

        return !self::isVerifiedSearchBot($ip, $ua);
    }

    /** Reverse + forward DNS check, cached for a day per IP. */
    private static function isVerifiedSearchBot(string $ip, string $ua): bool
    {
        $suffixes = null;
        foreach (self::VERIFIABLE as $pattern => $hosts) {
            if (preg_match($pattern, $ua) === 1) {
                $suffixes = $hosts;
                break;
            }
        }
        if ($suffixes === null) {
            return false;
        }

        return (bool)Yii::$app->cache->getOrSet('botguard.verified.' . $ip, static function () use ($ip, $suffixes): int {
            $host = @gethostbyaddr($ip);
            if (!is_string($host) || $host === $ip) {
                return 0;
            }
            $host = strtolower($host);
            $suffixOk = false;
            foreach ($suffixes as $suffix) {
                if (str_ends_with($host, $suffix)) {
                    $suffixOk = true;
                    break;
                }
            }
            if (!$suffixOk) {
                return 0;
            }
            $forward = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                ? array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6')
                : (@gethostbynamel($host) ?: []);

            return in_array($ip, $forward, true) ? 1 : 0;
        }, 86400);
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$net, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
        $ipBin = @inet_pton($ip);
        $netBin = @inet_pton((string)$net);
        if ($ipBin === false || $netBin === false || strlen($ipBin) !== strlen($netBin)) {
            return false;
        }
        $bits = $bits === null ? strlen($ipBin) * 8 : (int)$bits;
        $bytes = intdiv($bits, 8);
        if (strncmp($ipBin, $netBin, $bytes) !== 0) {
            return false;
        }
        $rem = $bits % 8;
        if ($rem === 0) {
            return true;
        }
        $mask = (0xFF << (8 - $rem)) & 0xFF;

        return (ord($ipBin[$bytes]) & $mask) === (ord($netBin[$bytes]) & $mask);
    }
}
