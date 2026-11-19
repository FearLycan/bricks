<?php

namespace console\controllers;

use common\components\IpRangeSet;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * IP range lists for frontend\components\BotGuard.
 *
 *   php yii bot-guard/update-ranges     # weekly cron
 *   php yii bot-guard/check 1.2.3.4     # which list an address is on
 *
 * datacenter    cloud / hosting networks: AWS, Google Cloud, Oracle,
 *               DigitalOcean (official lists) + X4BNet's aggregated
 *               datacenter list (Azure, Alibaba, Tencent, Hetzner, OVH, ...)
 * private-relay iCloud Private Relay egress addresses: real Safari users,
 *               exempted from the datacenter rule
 *
 * A source that fails to download keeps the previous file untouched only if
 * every source failed; otherwise the lists are rebuilt from what arrived.
 */
class BotGuardController extends Controller
{
    private const DATACENTER_SOURCES = [
        'aws'          => 'https://ip-ranges.amazonaws.com/ip-ranges.json',
        'gcp'          => 'https://www.gstatic.com/ipranges/cloud.json',
        'oracle'       => 'https://docs.oracle.com/iaas/tools/public_ip_ranges.json',
        'digitalocean' => 'https://digitalocean.com/geo/google.csv',
        'x4bnet'       => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv4.txt',
    ];

    private const PRIVATE_RELAY_SOURCE = 'https://mask-api.icloud.com/egress-ip-ranges.csv';

    public function actionUpdateRanges(): int
    {
        $dir = Yii::getAlias('@console/runtime/botguard');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->stderr("Cannot create {$dir}\n");
            return ExitCode::CANTCREAT;
        }

        $cidrs = [];
        foreach (self::DATACENTER_SOURCES as $name => $url) {
            $body = $this->download($url);
            if ($body === null) {
                $this->stderr("  {$name}: download failed\n");
                continue;
            }
            $found = $this->parse($name, $body);
            $this->stdout(sprintf("  %-12s %6d prefixes\n", $name, count($found)));
            array_push($cidrs, ...$found);
        }
        if ($cidrs === []) {
            $this->stderr("No datacenter list downloaded, keeping the previous files.\n");
            return ExitCode::UNAVAILABLE;
        }
        $written = IpRangeSet::build($dir . '/datacenter', $cidrs);
        $this->stdout("datacenter: {$written['v4']} IPv4 + {$written['v6']} IPv6 merged ranges\n");

        $relay = $this->download(self::PRIVATE_RELAY_SOURCE);
        if ($relay !== null) {
            $prefixes = [];
            foreach (preg_split('/\R/', $relay) as $line) {
                $first = strtok($line, ',');
                if ($first !== false && str_contains($first, '/')) {
                    $prefixes[] = $first;
                }
            }
            $written = IpRangeSet::build($dir . '/private-relay', $prefixes);
            $this->stdout("private-relay: {$written['v4']} IPv4 + {$written['v6']} IPv6 merged ranges\n");
        } else {
            $this->stderr("private-relay: download failed, keeping the previous files\n");
        }

        return ExitCode::OK;
    }

    public function actionCheck(string $ip): int
    {
        $dir = Yii::getAlias('@console/runtime/botguard');
        foreach (['datacenter', 'private-relay'] as $name) {
            $this->stdout(sprintf("%-14s %s\n", $name, (new IpRangeSet("{$dir}/{$name}"))->contains($ip) ? 'yes' : 'no'));
        }

        return ExitCode::OK;
    }

    /** @return string[] */
    private function parse(string $name, string $body): array
    {
        switch ($name) {
            case 'aws':
                $data = json_decode($body, true) ?: [];
                return array_merge(
                    array_column($data['prefixes'] ?? [], 'ip_prefix'),
                    array_column($data['ipv6_prefixes'] ?? [], 'ipv6_prefix')
                );
            case 'gcp':
                $out = [];
                foreach ((json_decode($body, true) ?: [])['prefixes'] ?? [] as $p) {
                    $out[] = $p['ipv4Prefix'] ?? $p['ipv6Prefix'] ?? '';
                }
                return array_filter($out);
            case 'oracle':
                $out = [];
                foreach ((json_decode($body, true) ?: [])['regions'] ?? [] as $region) {
                    foreach ($region['cidrs'] ?? [] as $c) {
                        $out[] = $c['cidr'] ?? '';
                    }
                }
                return array_filter($out);
            case 'digitalocean':
                $out = [];
                foreach (preg_split('/\R/', $body) as $line) {
                    $first = strtok($line, ',');
                    if ($first !== false && str_contains($first, '/')) {
                        $out[] = $first;
                    }
                }
                return $out;
            default:
                return array_values(array_filter(array_map('trim', preg_split('/\R/', $body)),
                    static fn($l) => $l !== '' && $l[0] !== '#'));
        }
    }

    private function download(string $url): ?string
    {
        $ctx = stream_context_create(['http' => [
            'timeout'       => 60,
            'user_agent'    => 'BrickAtlas BotGuard range updater (+https://brickatlas.shop)',
            'ignore_errors' => false,
        ]]);
        $body = @file_get_contents($url, false, $ctx);

        return is_string($body) && $body !== '' ? $body : null;
    }
}
