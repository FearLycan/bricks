<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use common\components\TrafficReport;
use Yii;
use yii\web\Response;

/**
 * Cookieless traffic stats (recorded by frontend\components\TrafficRecorder).
 * The page is a shell; data comes from actionData, so changing the period never reloads.
 */
class TrafficController extends Controller
{
    /** Allowed periods in days (1 = today only). */
    public const RANGES = [1, 7, 30, 90, 365];

    public function actionIndex(int $days = 30): string
    {
        return $this->render('index', ['days' => self::clamp($days), 'ranges' => self::RANGES]);
    }

    /** GET /admin/traffic/data?days=N */
    public function actionData(int $days = 30): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Cache-Control', 'no-store');

        return (new TrafficReport(self::clamp($days)))->toArray();
    }

    private static function clamp(int $days): int
    {
        return in_array($days, self::RANGES, true) ? $days : 30;
    }
}
