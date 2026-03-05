<?php

namespace frontend\components;

use common\models\Set;
use common\models\Theme;
use Yii;
use yii\helpers\Url;

final class SeoHelper
{
    private const DEFAULT_META_DESCRIPTION = 'Browse LEGO sets, compare prices, explore themes, and find minifigure appearances in the Brick Store catalog.';

    public static function resolvePageNumber(): int
    {
        return max(1, (int)Yii::$app->request->get('page', 1));
    }

    public static function hasActiveCatalogFilters(array $queryParams): bool
    {
        foreach (['name', 'theme_id', 'subtheme_id', 'sort_option', 'year'] as $key) {
            if (!array_key_exists($key, $queryParams)) {
                continue;
            }

            $value = $queryParams[$key];
            if (is_string($value) && trim($value) === '') {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            return true;
        }

        return false;
    }

    public static function defaultMetaDescription(): string
    {
        return self::DEFAULT_META_DESCRIPTION;
    }

    public static function normalizeText(?string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim(strip_tags((string)$value)));

        return html_entity_decode($normalized ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function truncate(string $value, int $limit = 160): string
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) <= $limit) {
            return $value;
        }

        $cut = mb_substr($value, 0, $limit + 1);
        $lastSpace = mb_strrpos($cut, ' ');
        if ($lastSpace !== false && $lastSpace >= (int)floor($limit * 0.6)) {
            $cut = mb_substr($cut, 0, $lastSpace);
        } else {
            $cut = mb_substr($cut, 0, $limit);
        }

        return rtrim($cut, " \t\n\r\0\x0B.,;:-") . '...';
    }

    public static function buildAbsoluteUrl(string|array $url): string
    {
        if (is_string($url) && preg_match('~^https?://~i', $url) === 1) {
            return $url;
        }

        return Url::to($url, true);
    }

    public static function buildCatalogTitle(int $page = 1): string
    {
        return self::appendPageSuffix('LEGO Sets Catalog and Price Comparison', $page);
    }

    public static function buildCatalogDescription(int $page = 1): string
    {
        $description = 'Browse LEGO sets, compare prices, and filter the catalog by theme, release year, and sorting options.';

        return self::appendPageDescriptionSuffix(self::truncate($description), $page);
    }

    public static function buildFilteredCatalogTitle(): string
    {
        return 'Filtered LEGO Sets Results';
    }

    public static function buildFilteredCatalogDescription(): string
    {
        return self::truncate('Filtered LEGO set results for the current catalog view. Refine the listing by keyword, theme, release year, and sorting options.');
    }

    public static function buildCatalogIntro(): string
    {
        return 'Explore the latest LEGO sets, compare prices, and quickly narrow the catalog by theme, release year, or sorting preferences.';
    }

    public static function buildThemeTitle(Theme $theme, ?Theme $subTheme = null, int $page = 1): string
    {
        $name = self::normalizeText($subTheme?->name ?? $theme->name);

        return self::appendPageSuffix($name . ' LEGO Sets and Price Comparison', $page);
    }

    public static function buildThemeDescription(Theme $theme, ?Theme $subTheme = null, int $page = 1): string
    {
        $targetTheme = $subTheme ?? $theme;
        $name = self::normalizeText($targetTheme->name);
        $parts = ['Browse ' . $name . ' LEGO sets with current prices, release years, piece counts, and minifigure details.'];

        if ($targetTheme->sets_count) {
            $parts[] = 'This category currently lists ' . (int)$targetTheme->sets_count . ' sets.';
        }

        if ($targetTheme->year_from && $targetTheme->year_to) {
            $parts[] = 'The range covers releases from ' . (int)$targetTheme->year_from . ' to ' . (int)$targetTheme->year_to . '.';
        }

        return self::appendPageDescriptionSuffix(self::truncate(implode(' ', $parts)), $page);
    }

    public static function buildThemeIntro(Theme $theme, ?Theme $subTheme = null): string
    {
        $targetTheme = $subTheme ?? $theme;
        $description = self::normalizeText($targetTheme->description);
        if ($description !== '') {
            return self::truncate($description, 260);
        }

        return self::buildThemeDescription($theme, $subTheme);
    }

    public static function buildSetTitle(Set $set): string
    {
        $setName = self::normalizeText($set->name);
        $setNumber = self::normalizeText($set->getSetNumberText());

        return $setName . ' LEGO Set ' . $setNumber . ' - Price Comparison and Details';
    }

    public static function buildSetDescription(Set $set): string
    {
        $description = self::normalizeText($set->description);
        if ($description !== '') {
            return self::truncate($description);
        }

        $details = [];
        $themeName = self::normalizeText($set->theme->name ?? null);
        if ($themeName !== '') {
            $details[] = $themeName . ' theme';
        }

        if ($set->year) {
            $details[] = 'released in ' . (int)$set->year;
        }

        if ($set->pieces) {
            $details[] = (int)$set->pieces . ' pieces';
        }

        if ($set->minifigures) {
            $details[] = (int)$set->minifigures . ' minifigures';
        }

        $summary = 'Compare prices and details for LEGO set ' . self::normalizeText($set->name) . ' (' . self::normalizeText($set->getSetNumberText()) . ').';
        if ($details !== []) {
            $summary .= ' Includes ' . implode(', ', $details) . '.';
        }

        return self::truncate($summary);
    }

    public static function buildMinifigTitle(string $displayName, int $page = 1): string
    {
        return self::appendPageSuffix('LEGO Sets with Minifigure: ' . self::normalizeText($displayName), $page);
    }

    public static function buildMinifigDescription(string $displayName, string $number, int $page = 1): string
    {
        $description = self::truncate('Browse LEGO sets that include minifigure ' . self::normalizeText($displayName) . ' and compare current offers for minifigure number ' . self::normalizeText($number) . '.');

        return self::appendPageDescriptionSuffix($description, $page);
    }

    private static function appendPageSuffix(string $value, int $page): string
    {
        if ($page <= 1) {
            return $value;
        }

        return $value . ' - Page ' . $page;
    }

    private static function appendPageDescriptionSuffix(string $value, int $page): string
    {
        if ($page <= 1) {
            return $value;
        }

        return self::truncate($value . ' Page ' . $page . '.');
    }
}
