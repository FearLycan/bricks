<?php

namespace frontend\components;

use common\models\Set;
use common\models\Theme;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\View;

final class SeoHelper
{
    public const SUPPORTED_LANGUAGES = ['en', 'pl', 'de', 'fr', 'es', 'it', 'ja', 'zh'];
    public const DEFAULT_HREFLANG_LANGUAGE = 'en';

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
        return T::tr('Browse LEGO sets, compare prices, explore themes and find minifigure appearances on BrickAtlas.');
    }

    /**
     * Build the same URL for every supported language plus an `x-default` entry,
     * used to emit `<link rel="alternate" hreflang="…">` tags.
     *
     * Pass `null` to build alternates for the *current* request — this works
     * for routes with path-mapped parameters (e.g. `/lego/<slug>`) where
     * the parameters are not present in `queryParams`. Pass an explicit
     * `Url::to`-style array to build alternates for an arbitrary route.
     *
     * @return array<string,string> map of hreflang code → absolute URL
     */
    public static function buildHreflangUrls(array|string|null $urlParts = null): array
    {
        $result = [];

        // When building from the current request, drop pagination params that
        // equal 1 so the hreflang URLs stay consistent with the canonical URL
        // (page 1 is always the bare URL, never `?page=1`).
        $currentParams = [];
        if ($urlParts === null) {
            foreach (['page', 'promo_page'] as $pageParam) {
                if ((int)Yii::$app->request->get($pageParam) === 1) {
                    $currentParams[$pageParam] = null;
                }
            }
        }

        foreach (self::SUPPORTED_LANGUAGES as $lang) {
            if ($urlParts === null) {
                $result[$lang] = Url::current(['language' => $lang] + $currentParams, true);
            } else {
                $parts = is_array($urlParts) ? $urlParts : [$urlParts];
                $parts['language'] = $lang;
                $result[$lang] = Url::to($parts, true);
            }
        }

        $result['x-default'] = $result[self::DEFAULT_HREFLANG_LANGUAGE];

        return $result;
    }

    /**
     * Build a URL for the current request in the given language. Use this for the
     * language-switcher dropdown so the user lands on the same page in the new
     * language.
     */
    public static function buildCurrentUrlInLanguage(string $language, bool $absolute = false): string
    {
        return Url::current(['language' => $language], $absolute);
    }

    /**
     * Rewrite the language prefix in an existing relative URL string.
     * Used for redirects where we only have a raw path (returnUrl, current URL)
     * and want to switch languages without re-running URL generation.
     */
    public static function rewriteUrlLanguage(string $url, string $newLanguage): string
    {
        $queryStart = strpos($url, '?');
        if ($queryStart !== false) {
            $path = substr($url, 0, $queryStart);
            $query = substr($url, $queryStart);
        } else {
            $path = $url;
            $query = '';
        }

        $pattern = '#^/(?:' . implode('|', self::SUPPORTED_LANGUAGES) . ')(?=/|$)#';
        $path = preg_replace($pattern, '', (string)$path, 1) ?? $path;
        if ($path === '') {
            $path = '/';
        }

        if ($newLanguage !== self::DEFAULT_HREFLANG_LANGUAGE) {
            $path = '/' . $newLanguage . ($path === '/' ? '' : $path);
        }

        return $path . $query;
    }

    /**
     * Emit hreflang link tags for the given route. Pass `null` to use the current request.
     */
    public static function registerHreflangLinks(View $view, array|string|null $urlParts = null): void
    {
        $urls = self::buildHreflangUrls($urlParts);
        foreach ($urls as $hreflang => $href) {
            $view->registerLinkTag([
                'rel'      => 'alternate',
                'hreflang' => $hreflang,
                'href'     => $href,
            ], 'hreflang-' . $hreflang);
        }
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
        return self::appendPageSuffix(T::tr('LEGO Sets Catalog and Price Comparison'), $page);
    }

    public static function buildPromoTitle(int $page = 1): string
    {
        return self::appendPageSuffix(T::tr('LEGO Sets On Sale'), $page);
    }

    public static function buildPromoDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO sets currently on sale. Find the best discounts and compare prices from top retailers.');

        return self::truncate(self::appendPageDescriptionSuffix($description, $page));
    }

    public static function buildMagazinesTitle(int $page = 1): string
    {
        return self::appendPageSuffix(T::tr('LEGO Magazine Sets'), $page);
    }

    public static function buildMagazinesDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO mini-build sets distributed with collectible magazines. Compare prices and discover the full magazine-gift catalog.');

        return self::truncate(self::appendPageDescriptionSuffix($description, $page));
    }

    public static function buildMagazinesIntro(): string
    {
        return T::tr('Small bonus builds bundled with collectible LEGO magazines — handy minifigure packs, vehicles and seasonal scenes from kiosk releases.');
    }

    public static function buildExclusiveTitle(int $page = 1): string
    {
        return self::appendPageSuffix(T::tr('LEGO Exclusive Sets'), $page);
    }

    public static function buildExclusiveDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO sets sold exclusively through LEGO.com and brand stores. Compare prices and track availability for collector-grade releases.');

        return self::truncate(self::appendPageDescriptionSuffix($description, $page));
    }

    public static function buildExclusiveIntro(): string
    {
        return T::tr('Sets sold only through official LEGO channels — usually larger, collector-focused releases that disappear from shelves fast.');
    }

    public static function buildRetiringSoonTitle(int $page = 1): string
    {
        return self::appendPageSuffix(T::tr('LEGO Sets Retiring Soon'), $page);
    }

    public static function buildRetiringSoonDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO sets approaching their official retirement date. Catch them before they leave shelves and prices climb.');

        return self::truncate(self::appendPageDescriptionSuffix($description, $page));
    }

    public static function buildRetiringSoonIntro(): string
    {
        return T::tr('Sets with a confirmed retirement date — likely to disappear from retailers and climb in price once gone.');
    }

    public static function buildCatalogDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO sets and filter the catalog by theme, release year, or sort by price.');

        return self::appendPageDescriptionSuffix(self::truncate($description), $page);
    }

    public static function buildFilteredCatalogTitle(): string
    {
        return T::tr('Filtered LEGO Sets Results');
    }

    public static function buildFilteredCatalogDescription(): string
    {
        return self::truncate(T::tr('Your filtered catalog view. Refine by keyword, theme, year or sort order.'));
    }

    public static function buildCatalogIntro(): string
    {
        return T::tr('Browse the catalog and filter by theme, year or sort by price.');
    }

    public static function buildThemeTitle(Theme $theme, ?Theme $subTheme = null, int $page = 1): string
    {
        $name = self::normalizeText($subTheme?->name ?? $theme->name);

        return self::appendPageSuffix(T::tr('{name} LEGO Sets and Price Comparison', ['name' => $name]), $page);
    }

    public static function buildThemeDescription(Theme $theme, ?Theme $subTheme = null, int $page = 1): string
    {
        $targetTheme = $subTheme ?? $theme;
        $name = self::normalizeText($targetTheme->name);
        $parts = [T::tr('Browse {name} LEGO sets with current prices, release years, piece counts, and minifigure details.', ['name' => $name])];

        if ($targetTheme->sets_count) {
            $parts[] = T::tr('This category currently lists {count} sets.', ['count' => (int)$targetTheme->sets_count]);
        }

        if ($targetTheme->year_from && $targetTheme->year_to) {
            $parts[] = T::tr('The range covers releases from {from} to {to}.', [
                'from' => (int)$targetTheme->year_from,
                'to'   => (int)$targetTheme->year_to,
            ]);
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

        return T::tr('{name} LEGO Set {number} - Price Comparison and Details', [
            'name'   => $setName,
            'number' => $setNumber,
        ]);
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
            $details[] = T::tr('{theme} theme', ['theme' => $themeName]);
        }

        if ($set->year) {
            $details[] = T::tr('released in {year}', ['year' => (int)$set->year]);
        }

        if ($set->pieces) {
            $details[] = T::tr('{n} pieces', ['n' => (int)$set->pieces]);
        }

        if ($set->minifigures) {
            $details[] = T::tr('{n} minifigures', ['n' => (int)$set->minifigures]);
        }

        $summary = T::tr('Compare prices and details for LEGO set {name} ({number}).', [
            'name'   => self::normalizeText($set->name),
            'number' => self::normalizeText($set->getSetNumberText()),
        ]);
        if ($details !== []) {
            $summary .= ' ' . T::tr('Includes {details}.', ['details' => implode(', ', $details)]);
        }

        return self::truncate($summary);
    }

    public static function buildMinifigTitle(string $displayName, int $page = 1): string
    {
        return self::appendPageSuffix(T::tr('LEGO Sets with Minifigure: {name}', ['name' => self::normalizeText($displayName)]), $page);
    }

    public static function buildMinifigDescription(string $displayName, string $number, int $page = 1): string
    {
        $description = self::truncate(T::tr('Browse LEGO sets with minifigure {name} ({number}) and compare current offers.', [
            'name'   => self::normalizeText($displayName),
            'number' => self::normalizeText($number),
        ]));

        return self::appendPageDescriptionSuffix($description, $page);
    }

    public static function registerPaginationLinks(View $view, ActiveDataProvider $dataProvider, int $page, array $baseUrlParts, string $pageParam = 'page'): void
    {
        $pagination = $dataProvider->getPagination();
        if ($pagination === false) {
            return;
        }

        $totalPages = (int)$pagination->getPageCount();

        if ($page > 1) {
            $prevUrl = $page === 2
                ? self::buildAbsoluteUrl($baseUrlParts)
                : self::buildAbsoluteUrl(array_merge($baseUrlParts, [$pageParam => $page - 1]));
            $view->registerLinkTag(['rel' => 'prev', 'href' => $prevUrl], 'pagination-prev');
        }

        if ($page < $totalPages) {
            $view->registerLinkTag([
                'rel'  => 'next',
                'href' => self::buildAbsoluteUrl(array_merge($baseUrlParts, [$pageParam => $page + 1])),
            ], 'pagination-next');
        }
    }

    private static function appendPageSuffix(string $value, int $page): string
    {
        if ($page <= 1) {
            return $value;
        }

        return $value . ' - ' . T::tr('Page {page}', ['page' => $page]);
    }

    private static function appendPageDescriptionSuffix(string $value, int $page): string
    {
        if ($page <= 1) {
            return $value;
        }

        return self::truncate($value . ' ' . T::tr('Page {page}', ['page' => $page]) . '.');
    }
}
