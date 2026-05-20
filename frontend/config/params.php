<?php
return [
    'gtag'        => '',
    'leadTag'     => '',
    'smart-links' => [],

    // Homepage cache TTL in seconds. Override per-environment in params-local.php.
    // Set any to 0 to bypass the cache entirely (recomputes on every request).
    'homepage.cache.catalog'  => 600,   // 10 min — new/top/coming/adults
    'homepage.cache.onSale'   => 300,   // 5 min — prices move faster
    'homepage.cache.branding' => 1800,  // 30 min — hero, themes, minifigs
    'homepage.cache.personal' => 60,    // 60 s   — per-user personalization
];
