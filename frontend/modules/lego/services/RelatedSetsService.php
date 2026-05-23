<?php

namespace frontend\modules\lego\services;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetTag;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Picks sets thematically related to a given source set.
 *
 * Scoring is a sum of independent signals (see weights below); higher score
 * means stronger relationship. The minimum bar is one positive signal — a set
 * with score 0 is dropped so the slider never fills with random catalog noise.
 */
final class RelatedSetsService
{
    private const WEIGHT_SUBTHEME      = 10;
    private const WEIGHT_THEME         = 5;
    private const WEIGHT_PIECES_WINDOW = 3;
    private const WEIGHT_AGE           = 2;
    private const WEIGHT_TAG           = 2;

    private const PIECES_WINDOW_MIN_FRACTION = 0.5;
    private const PIECES_WINDOW_MAX_FRACTION = 2.0;

    private const CACHE_TTL = 1800;

    /**
     * @return Set[]
     */
    public function getRelatedSets(Set $source, int $limit = 12): array
    {
        $limit = max(1, $limit);
        $cacheKey = "lego.relatedSets.{$source->id}.{$limit}";

        $ids = Yii::$app->cache->getOrSet($cacheKey, function () use ($source, $limit): array {
            return $this->resolveRelatedIds($source, $limit);
        }, self::CACHE_TTL);

        if (!is_array($ids) || $ids === []) {
            return [];
        }

        $sets = Set::find()
            ->where(['id' => $ids])
            ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
            ->indexBy('id')
            ->all();

        // Preserve the score order we resolved earlier.
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($sets[$id])) {
                $ordered[] = $sets[$id];
            }
        }

        return $ordered;
    }

    /**
     * @return int[]
     */
    private function resolveRelatedIds(Set $source, int $limit): array
    {
        $tagIds = $this->collectTagIds($source);
        $piecesWindow = $this->buildPiecesWindow($source);

        $score = $this->buildScoreExpression($source, $tagIds, $piecesWindow);

        $query = (new Query())
            ->select(['s.id', 'score' => $score])
            ->from(['s' => Set::tableName()])
            ->where(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['<>', 's.id', (int)$source->id])
            ->having(['>', 'score', 0])
            ->orderBy(new Expression('score DESC, s.rating IS NULL ASC, s.rating DESC, s.id DESC'))
            ->limit($limit);

        $rows = $query->all();

        return array_map(static fn(array $row): int => (int)$row['id'], $rows);
    }

    /**
     * @return int[]
     */
    private function collectTagIds(Set $source): array
    {
        $ids = SetTag::find()
            ->select('tag_id')
            ->where(['set_id' => (int)$source->id])
            ->column();

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * @return array{0:int,1:int}|null [min, max] piece-count window, or null when the source has no piece data.
     */
    private function buildPiecesWindow(Set $source): ?array
    {
        if (!$source->pieces || $source->pieces <= 0) {
            return null;
        }

        $min = (int)floor($source->pieces * self::PIECES_WINDOW_MIN_FRACTION);
        $max = (int)ceil($source->pieces * self::PIECES_WINDOW_MAX_FRACTION);

        return [max(1, $min), $max];
    }

    /**
     * @param int[]                 $tagIds
     * @param array{0:int,1:int}|null $piecesWindow
     */
    private function buildScoreExpression(Set $source, array $tagIds, ?array $piecesWindow): Expression
    {
        $parts = [];
        $params = [];

        if ($source->subtheme_id !== null) {
            $parts[] = "(CASE WHEN s.subtheme_id = :rel_sub THEN " . self::WEIGHT_SUBTHEME . " ELSE 0 END)";
            $params[':rel_sub'] = (int)$source->subtheme_id;
        }

        $parts[] = "(CASE WHEN s.theme_id = :rel_theme THEN " . self::WEIGHT_THEME . " ELSE 0 END)";
        $params[':rel_theme'] = (int)$source->theme_id;

        if ($source->age !== null) {
            $parts[] = "(CASE WHEN s.age = :rel_age THEN " . self::WEIGHT_AGE . " ELSE 0 END)";
            $params[':rel_age'] = (int)$source->age;
        }

        if ($piecesWindow !== null) {
            $parts[] = "(CASE WHEN s.pieces BETWEEN :rel_pmin AND :rel_pmax THEN " . self::WEIGHT_PIECES_WINDOW . " ELSE 0 END)";
            $params[':rel_pmin'] = $piecesWindow[0];
            $params[':rel_pmax'] = $piecesWindow[1];
        }

        if ($tagIds !== []) {
            $tagPlaceholders = [];
            foreach ($tagIds as $index => $id) {
                $placeholder = ':rel_tag_' . $index;
                $tagPlaceholders[] = $placeholder;
                $params[$placeholder] = $id;
            }
            $tagList = implode(', ', $tagPlaceholders);
            $parts[] = "(COALESCE((SELECT COUNT(*) FROM {{%set_tag}} st_rel WHERE st_rel.set_id = s.id AND st_rel.tag_id IN ($tagList)), 0) * " . self::WEIGHT_TAG . ")";
        }

        $sql = implode(' + ', $parts);

        return new Expression($sql, $params);
    }
}
