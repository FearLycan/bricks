<?php

/**
 * Scheduled console tasks, run by `php yii schedule/run` from ONE crontab line:
 *
 *   * * * * *  cd /home/papugo/domains/brickatlas.shop && php yii schedule/run >> console/runtime/logs/schedule.log 2>&1
 *
 * Change timing or add tasks here (and deploy); the crontab never changes again.
 * Format: name => [cron expression (server time), console route with arguments].
 * Each task logs to console/runtime/logs/schedule-<name>.log and is skipped while its
 * previous run is still going. Preview: php yii schedule/list
 *
 * Timings copied from the crontab of 27.11.2026. Not scheduled on purpose (were commented
 * out there): set-offer-import/process-pending, set-offer-discovery/queue-matches,
 * ali-express-review/sync (AliExpress offers stay off the site).
 */
return [
    // Brickset: sets changed recently (prices, dates, images), every 4 hours.
    'brickset'   => ['15 */4 * * *', 'brickset/sync-recently-updated-sets'],
    // Note: a console cache flush clears console/runtime/cache only, not the frontend cache.
    'cache'      => ['25 4 * * *', 'cache/flush-all'],
    'sitemap'    => ['20 6 * * *', 'sitemap/generate'],
    // Data-centre IP ranges for BotGuard, Mondays.
    'bot-ranges' => ['10 4 * * 1', 'bot-guard/update-ranges'],
    // New: email alerts for quiet failures (stale Brickset data, old sitemap, error waves).
    'health'     => ['45 * * * *', 'health/check'],
    // New: trim *.log files over 5 MB to the last 5000 lines, Sundays.
    'log-trim'   => ['30 5 * * 0', 'schedule/trim-logs'],
];
