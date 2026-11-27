<?php

/**
 * Scheduled console tasks, run by `php yii schedule/run` from ONE crontab line:
 *
 *   * * * * *  cd /path/to/brickatlas && php yii schedule/run >> console/runtime/logs/schedule.log 2>&1
 *
 * Change timing or add tasks here (and deploy); the crontab never changes again.
 * Format: name => [cron expression (server time), console route with arguments].
 * Each task logs to console/runtime/logs/schedule-<name>.log and is skipped while its
 * previous run is still going. Preview: php yii schedule/list
 */
return [
    // Brickset: sets changed in the last days (prices, dates, images), then themes weekly.
    'brickset'     => ['10 2 * * *', 'brickset/sync-recently-updated-sets'],
    'themes'       => ['40 2 * * 1', 'brickset/sync-themes'],
    // Minifigures from Rebrickable.
    'minifigs'     => ['20 3 * * *', 'rebrickable/sync-minifigs'],
    // Set counts on tags and themes (IndexPolicy thresholds read them), before the sitemap.
    'tag-count'    => ['10 4 * * *', 'tag/recount-sets'],
    'theme-count'  => ['15 4 * * *', 'theme/recount-sets'],
    'sitemap'      => ['40 4 * * *', 'sitemap/generate'],
    // Data-centre IP ranges for BotGuard.
    'bot-ranges'   => ['10 5 * * 1', 'bot-guard/update-ranges'],
    // Email alerts for quiet failures (stale Brickset data, old sitemap, error waves).
    'health'       => ['45 * * * *', 'health/check'],
    // Trim *.log files over 5 MB to the last 5000 lines.
    'log-trim'     => ['30 5 * * 0', 'schedule/trim-logs'],
];
