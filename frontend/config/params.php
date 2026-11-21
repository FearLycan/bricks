<?php
return [
    'gtag'        => '',
    'leadTag'     => '',
    'pagead2'     => '',

    // Homepage cache TTL in seconds. Override per-environment in params-local.php.
    // Set any to 0 to bypass the cache entirely (recomputes on every request).
    'homepage.cache.catalog'  => 600,   // 10 min — new/top/coming/adults
    'homepage.cache.onSale'   => 300,   // 5 min — prices move faster
    'homepage.cache.branding' => 1800,  // 30 min — hero, themes, minifigs
    'homepage.cache.personal' => 60,    // 60 s   — per-user personalization

    // Bot diagnostics: page-view fingerprints in @runtime/fingerprints until
    // this moment (UTC), then off. '' = off. See RequestFingerprintLog.
    'fingerprintLog.until' => '2026-11-23 12:00:00 UTC',

    // BotGuard (see frontend\components\BotGuard): networks answered with 403
    // (abusive scrapers seen in the fingerprint log, 11.2026) and page views
    // allowed per IP per 5 minutes before 429 (verified search bots exempt).
    'botGuard.blockCidrs' => [
        '34.89.140.28/32',   // Google Cloud, ~4.7k views/day on the same pages, fake Android UA
        '43.172.0.0/15',     // Tencent Cloud
        '47.79.0.0/16',      // Alibaba Cloud
        '47.82.0.0/16',      // Alibaba Cloud
    ],
    'botGuard.rateLimit' => 60,
    // Admin read-only SQL endpoint (/management/db-query). Enable in params-local.php.
    'dbQuery.enabled'    => false,
];
