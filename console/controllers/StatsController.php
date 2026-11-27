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
     */
    public function actionPrune(int $days = 400): int
    {
        $cutoff = date('Y-m-d', strtotime("-{$days} days"));
        foreach (['traffic_path_daily', 'traffic_referrer_daily', 'traffic_bot_daily', 'search_daily'] as $table) {
            $n = Yii::$app->db->createCommand()->delete('{{%' . $table . '}}', ['<', 'day', $cutoff])->execute();
            $this->stdout("{$table}: deleted {$n} rows before {$cutoff}\n");
        }

        return ExitCode::OK;
    }
}
