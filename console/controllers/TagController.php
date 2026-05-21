<?php

namespace console\controllers;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetTag;
use common\models\Tag;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

final class TagController extends Controller
{
    /**
     * Recompute tag.sets_count for every tag as the number of ACTIVE sets
     * attached to it through the set_tag table.
     *
     * Usage:
     *   php yii tag/recount-sets
     */
    public function actionRecountSets(): int
    {
        $startedAt = microtime(true);

        $sql = 'UPDATE ' . Tag::tableName() . ' t SET t.sets_count = ('
            . 'SELECT COUNT(*) FROM ' . SetTag::tableName() . ' st'
            . ' INNER JOIN ' . Set::tableName() . ' s ON s.id = st.set_id AND s.status = :active'
            . ' WHERE st.tag_id = t.id'
            . ')';

        $affected = Yii::$app->db->createCommand($sql, [
            ':active' => StatusEnum::ACTIVE->value,
        ])->execute();

        $elapsedMs = (int)round((microtime(true) - $startedAt) * 1000);

        $populated = (int)Tag::find()
            ->alias('t')
            ->where(['>', 't.sets_count', 0])
            ->count('t.id');

        echo "Updated rows: {$affected}\n";
        echo "Tags with sets_count > 0: {$populated}\n";
        echo "Elapsed: {$elapsedMs} ms\n";

        return ExitCode::OK;
    }
}
