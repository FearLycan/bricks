<?php

namespace console\controllers;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\Theme;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

final class ThemeController extends Controller
{
    /**
     * Recompute theme.sets_count for every theme as the number of ACTIVE sets
     * directly attached to it (s.theme_id = t.id).
     *
     * Usage:
     *   php yii theme/recount-sets
     */
    public function actionRecountSets(): int
    {
        $startedAt = microtime(true);

        $sql = 'UPDATE ' . Theme::tableName() . ' t SET t.sets_count = ('
            . 'SELECT COUNT(*) FROM ' . Set::tableName() . ' s'
            . ' WHERE s.theme_id = t.id AND s.status = :active'
            . ')';

        $affected = Yii::$app->db->createCommand($sql, [
            ':active' => StatusEnum::ACTIVE->value,
        ])->execute();

        $elapsedMs = (int)round((microtime(true) - $startedAt) * 1000);

        $populated = (int)Theme::find()
            ->alias('t')
            ->where(['not', ['t.sets_count' => null]])
            ->andWhere(['>', 't.sets_count', 0])
            ->count('t.id');

        echo "Updated rows: {$affected}\n";
        echo "Themes with sets_count > 0: {$populated}\n";
        echo "Elapsed: {$elapsedMs} ms\n";

        return ExitCode::OK;
    }
}
