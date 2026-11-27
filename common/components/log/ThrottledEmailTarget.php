<?php

declare(strict_types=1);

namespace common\components\log;

use Throwable;
use Yii;
use yii\log\EmailTarget;

/**
 * EmailTarget that sends at most one mail per `$interval` seconds, so an error storm (database
 * down, a broken deploy) means one mail with a count instead of one mail per request.
 * Messages dropped while throttled are counted and mentioned in the next mail. Ported from store-base.
 */
final class ThrottledEmailTarget extends EmailTarget
{
    public int $interval = 900;

    public function export(): void
    {
        try {
            $cache = Yii::$app->cache;
            $key = 'log.mail.' . md5((string) ($this->message['subject'] ?? ''));
            $state = (array) ($cache->get($key) ?: ['at' => 0, 'skipped' => 0]);
            if (time() - (int) $state['at'] < $this->interval) {
                $state['skipped'] = (int) $state['skipped'] + count($this->messages);
                $cache->set($key, $state, $this->interval * 4);
                return;
            }
            if ((int) $state['skipped'] > 0) {
                $this->messages[] = ["(+{$state['skipped']} more log message(s) were not mailed during the last " . intdiv($this->interval, 60) . ' min; see the log table / runtime/logs/app.log)', \yii\log\Logger::LEVEL_ERROR, 'application', microtime(true)];
            }
            $cache->set($key, ['at' => time(), 'skipped' => 0], $this->interval * 4);
        } catch (Throwable) {
            // No cache: fall through and send.
        }
        parent::export();
    }
}
