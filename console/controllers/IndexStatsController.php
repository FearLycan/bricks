<?php

namespace console\controllers;

use common\components\IndexPolicy;
use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetMinifig;
use common\models\SetTag;
use common\models\Tag;
use common\models\Theme;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * How many tag / subtheme / minifigure pages each IndexPolicy threshold
 * would keep in the index. Read-only.
 *
 *   php yii index-stats
 */
class IndexStatsController extends Controller
{
    private const BUCKETS = [[1, 1], [2, 2], [3, 4], [5, 9], [10, 14], [15, 19], [20, 49], [50, PHP_INT_MAX]];

    public function actionIndex(): int
    {
        $db = Yii::$app->db;
        $active = StatusEnum::ACTIVE->value;
        $set = Set::tableName();

        $tagCounts = $db->createCommand(
            'SELECT COUNT(DISTINCT s.id) FROM ' . Tag::tableName() . ' t'
            . ' JOIN ' . SetTag::tableName() . ' st ON st.tag_id = t.id'
            . " JOIN {$set} s ON s.id = st.set_id AND s.status = :a"
            . ' WHERE t.status = :a GROUP BY t.id',
            [':a' => $active]
        )->queryColumn();

        $subthemeCounts = $db->createCommand(
            'SELECT COUNT(s.id) FROM ' . Theme::tableName() . ' th'
            . " JOIN {$set} s ON s.subtheme_id = th.id AND s.status = :a"
            . ' WHERE th.parent_id IS NOT NULL AND th.status = :a GROUP BY th.id',
            [':a' => $active]
        )->queryColumn();

        $minifigCounts = $db->createCommand(
            'SELECT COUNT(DISTINCT sm.set_id) FROM ' . SetMinifig::tableName() . ' sm'
            . " JOIN {$set} s ON s.id = sm.set_id AND s.status = :a"
            . " WHERE sm.number IS NOT NULL AND sm.number <> '' GROUP BY sm.number",
            [':a' => $active]
        )->queryColumn();

        $this->report('Tags', $tagCounts, IndexPolicy::MIN_TAG_SETS, [5, 10, 15, 20]);
        $this->report('Subthemes', $subthemeCounts, IndexPolicy::MIN_SUBTHEME_SETS, [5, 10]);
        $this->report('Minifigures', $minifigCounts, IndexPolicy::MIN_MINIFIG_SETS, [2, 3, 5]);

        $sets = (int)Set::find()->alias('s')->where(['s.status' => $active])->count();
        $indexableSets = (int)Set::find()->alias('s')->where(['s.status' => $active])
            ->andWhere(IndexPolicy::indexableSetCondition('s'))->count();
        $this->stdout("\nSets: {$sets} active, {$indexableSets} indexable (x7 languages = " . ($indexableSets * 7) . " URLs)\n");

        return ExitCode::OK;
    }

    /** @param array<int, string|int> $counts */
    private function report(string $label, array $counts, int $current, array $thresholds): void
    {
        $counts = array_map('intval', $counts);
        $this->stdout("\n{$label}: " . count($counts) . " with at least 1 active set (current threshold: {$current})\n");
        foreach (self::BUCKETS as [$min, $max]) {
            $n = count(array_filter($counts, static fn(int $c): bool => $c >= $min && $c <= $max));
            $range = $max === PHP_INT_MAX ? "{$min}+" : ($min === $max ? (string)$min : "{$min}-{$max}");
            $this->stdout(sprintf("  %-7s %6d\n", $range, $n));
        }
        foreach ($thresholds as $t) {
            $kept = count(array_filter($counts, static fn(int $c): bool => $c >= $t));
            $this->stdout(sprintf("  threshold >= %-3d keeps %6d pages (x7 languages = %d URLs)\n", $t, $kept, $kept * 7));
        }
    }
}
