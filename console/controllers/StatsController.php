<?php

declare(strict_types=1);

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/** Housekeeping for the traffic stats tables (ported from store-base). */
final class StatsController extends Controller
{
    /**
     * Drops per-path, referrer, crawler and search rows older than $days. The small
     * traffic_daily totals stay forever, so long-term trends survive.
     * Bot rows in offer_click go after 90 days.
     */
    public function actionPrune(int $days = 400): int
    {
        $cutoff = date('Y-m-d', strtotime("-{$days} days"));
        foreach (['traffic_path_daily', 'traffic_referrer_daily', 'traffic_bot_daily', 'search_daily'] as $table) {
            $n = Yii::$app->db->createCommand()->delete('{{%' . $table . '}}', ['<', 'day', $cutoff])->execute();
            $this->stdout("{$table}: deleted {$n} rows before {$cutoff}\n");
        }
        // Bot clicks are only kept to sanity-check the filter; human clicks stay.
        $n = Yii::$app->db->createCommand()->delete('{{%offer_click}}', ['and', ['is_bot' => 1], ['<', 'created_at', strtotime('-90 days')]])->execute();
        $this->stdout("offer_click: deleted {$n} bot clicks older than 90 days\n");

        return ExitCode::OK;
    }
}
