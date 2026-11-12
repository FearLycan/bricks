<?php

namespace common\components;

use common\enums\image\KindEnum;
use common\enums\image\TypeEnum;
use common\models\Set;
use common\models\SetImage;
use yii\db\Expression;

/**
 * Which catalog pages are worth indexing.
 *
 * Pages built only from imported catalog data (Brickset/Rebrickable) with
 * almost nothing on them read as thin content to Google and to the AdSense
 * review. They stay reachable for visitors but get `noindex,follow` and are
 * left out of the sitemap. Views and SitemapController both use this class,
 * so the two can never disagree.
 */
final class IndexPolicy
{
    /** A tag page needs at least this many active sets. */
    public const MIN_TAG_SETS = 5;

    /** A subtheme page needs at least this many active sets. */
    public const MIN_SUBTHEME_SETS = 5;

    /** A minifigure page needs to appear in at least this many sets. */
    public const MIN_MINIFIG_SETS = 2;

    public const INDEX = 'index,follow';
    public const NOINDEX = 'noindex,follow';

    /**
     * A set page is worth indexing when it is an actual build (has pieces;
     * gear, books and keychains have none) and has a main photo.
     */
    public static function isSetIndexable(Set $set): bool
    {
        return (int)$set->pieces >= 1 && $set->getMainImage() !== null;
    }

    public static function forSet(Set $set): string
    {
        return self::isSetIndexable($set) ? self::INDEX : self::NOINDEX;
    }

    public static function forTag(int $activeSets): string
    {
        return $activeSets >= self::MIN_TAG_SETS ? self::INDEX : self::NOINDEX;
    }

    public static function forMinifig(int $sets): string
    {
        return $sets >= self::MIN_MINIFIG_SETS ? self::INDEX : self::NOINDEX;
    }

    /** Main themes need at least one set; subthemes need MIN_SUBTHEME_SETS. */
    public static function forTheme(int $sets, bool $isSubtheme): string
    {
        return $sets >= ($isSubtheme ? self::MIN_SUBTHEME_SETS : 1) ? self::INDEX : self::NOINDEX;
    }

    /**
     * SQL condition matching isSetIndexable() for a set table alias.
     */
    public static function indexableSetCondition(string $alias = 's'): array
    {
        $image = SetImage::find()
            ->alias('ix_img')
            ->select(new Expression('1'))
            ->where("ix_img.set_id = {$alias}.id")
            ->andWhere(['ix_img.kind' => KindEnum::MAIN->value, 'ix_img.type' => TypeEnum::IMAGE->value]);

        return ['and', ['>=', "{$alias}.pieces", 1], ['exists', $image]];
    }
}
