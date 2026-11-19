<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Summary of the temporary page-view fingerprint log
 * (frontend\components\RequestFingerprintLog).
 *
 *   php yii fingerprint/report          # last 48 h
 *   php yii fingerprint/report 24 30    # last 24 h, 30 rows per table
 *
 * The output contains no full IPs except in the "top IPs" table, so it can be
 * pasted into a support chat if needed.
 */
class FingerprintController extends Controller
{
    public function actionReport(int $hours = 48, int $top = 25): int
    {
        $dir = Yii::getAlias('@frontend/runtime/fingerprints');
        $files = glob($dir . '/fp-*.jsonl') ?: [];
        if ($files === []) {
            $this->stdout("No fingerprint files in {$dir}\n");
            return ExitCode::OK;
        }

        $since = time() - $hours * 3600;
        $rows = [];
        foreach ($files as $file) {
            $fh = fopen($file, 'rb');
            while ($fh && ($line = fgets($fh)) !== false) {
                $r = json_decode($line, true);
                if (is_array($r) && strtotime((string)($r['t'] ?? '')) >= $since) {
                    $rows[] = $r;
                }
            }
            if ($fh) {
                fclose($fh);
            }
        }
        $total = count($rows);
        if ($total === 0) {
            $this->stdout("No page views in the last {$hours} h\n");
            return ExitCode::OK;
        }

        $ips = $prefix16 = $prefix24 = $uas = $anomaly = $ipPaths = $verdicts = $humanIps = [];
        $tagged = $withCookies = 0;
        foreach ($rows as $r) {
            $ip = (string)$r['ip'];
            $ips[$ip] = ($ips[$ip] ?? 0) + 1;
            $ipPaths[$ip][(string)$r['p']] = true;
            [$p16, $p24] = $this->prefixes($ip);
            $prefix16[$p16] = ($prefix16[$p16] ?? 0) + 1;
            $prefix24[$p24] = ($prefix24[$p24] ?? 0) + 1;
            $uas[$this->uaShort((string)$r['ua'])] = ($uas[$this->uaShort((string)$r['ua'])] ?? 0) + 1;

            $key = implode(' | ', [
                'lang:' . ($r['al'] !== '' ? 'y' : 'N'),
                'ch-ua:' . ($r['chua'] !== '' ? ($this->chUaMatches($r) ? 'ok' : 'MISMATCH') : 'none'),
                'sec-fetch:' . ($r['sfm'] !== '' ? 'y' : 'N'),
                'cookies:' . (!empty($r['ck']) ? 'y' : 'N'),
                str_replace('HTTP/', 'h', (string)$r['proto']),
            ]);
            $anomaly[$key] = ($anomaly[$key] ?? 0) + 1;
            $tagged += empty($r['bg']) ? 1 : 0;
            // Rows logged before BotGuard v2 (19.11.2026 ~10:00) carry no
            // verdict; keep them apart so they don't pass for "human".
            $v = (string)($r['bgr'] ?? 'before-v2');
            $verdicts[$v] = ($verdicts[$v] ?? 0) + 1;
            if ($v === 'human') {
                $humanIps[$ip] = ($humanIps[$ip] ?? 0) + 1;
            }
            $withCookies += !empty($r['ck']) ? 1 : 0;
        }

        $this->stdout(sprintf(
            "Page views: %d in last %d h | unique IPs: %d | without cookies: %d%% | BotGuard let tags load: %d%%\n",
            $total, $hours, count($ips), round(100 * ($total - $withCookies) / $total), round(100 * $tagged / $total)
        ));

        $this->table('BotGuard verdict (human = GA/AdSense load)', $verdicts, $top);
        $this->table('Top IPs judged human (check for missed bots)', $humanIps, 15, function (string $ip) use ($ipPaths, $rows): string {
            foreach ($rows as $r) {
                if ($r['ip'] === $ip) {
                    return count($ipPaths[$ip]) . ' pages  ' . $this->uaShort((string)$r['ua']);
                }
            }
            return '';
        });
        $this->table('Top IPs (views, distinct pages, UA)', $ips, $top, function (string $ip) use ($ipPaths, $rows): string {
            $ua = '';
            foreach ($rows as $r) {
                if ($r['ip'] === $ip) {
                    $ua = $this->uaShort((string)$r['ua']);
                    break;
                }
            }
            return count($ipPaths[$ip]) . " pages  {$ua}";
        });
        $this->table('Top /16 networks', $prefix16, $top);
        $this->table('Top /24 networks', $prefix24, $top);
        $this->table('User agents', $uas, $top);
        $this->table('Header profile (lang | client hints | sec-fetch | cookies | protocol)', $anomaly, $top);

        return ExitCode::OK;
    }

    /** @return array{0:string,1:string} */
    private function prefixes(string $ip): array
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $p = explode('.', $ip);
            return ["{$p[0]}.{$p[1]}.0.0/16", "{$p[0]}.{$p[1]}.{$p[2]}.0/24"];
        }
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return [$ip, $ip];
        }
        $hex = bin2hex($bin);
        $g = str_split($hex, 4);
        return [implode(':', array_slice($g, 0, 2)) . '::/32', implode(':', array_slice($g, 0, 3)) . '::/48'];
    }

    private function uaShort(string $ua): string
    {
        if ($ua === '') {
            return '(empty)';
        }
        $browser = preg_match('#(Edg|OPR|Firefox|Chrome|Version)/(\d+)#', $ua, $m) ? "{$m[1]}/{$m[2]}" : mb_substr($ua, 0, 40);
        $os = match (true) {
            str_contains($ua, 'Windows NT 10.0') => 'Win10/11',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'Mac OS X') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => '?',
        };
        if (preg_match('#bot|crawl|spider#i', $ua)) {
            $os .= ' [declared bot]';
        }
        return "{$browser} {$os}";
    }

    /** Chrome's Sec-CH-UA should name the same major version as its user agent. */
    private function chUaMatches(array $r): bool
    {
        if (!preg_match('#Chrome/(\d+)#', (string)$r['ua'], $m)) {
            return true;
        }
        return str_contains((string)$r['chua'], '"' . $m[1] . '"')
            || str_contains((string)$r['chua'], 'v="' . $m[1] . '"');
    }

    private function table(string $title, array $counts, int $top, ?callable $extra = null): void
    {
        arsort($counts);
        $total = array_sum($counts);
        $this->stdout("\n== {$title}\n");
        foreach (array_slice($counts, 0, $top, true) as $key => $n) {
            $this->stdout(sprintf(
                "%7d %5.1f%%  %s%s\n",
                $n, 100 * $n / $total, $key, $extra ? '  ' . $extra((string)$key) : ''
            ));
        }
    }
}
