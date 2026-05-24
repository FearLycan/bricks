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
        return T::tr('Browse LEGO sets, compare prices across retailers, explore themes and find minifigure appearances on BrickAtlas — your free LEGO price tracker.');
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

        // localeurls is configured with enableDefaultLanguageUrlCode=false, so
        // the default language URL has NO `/en` prefix — but Url::current /
        // Url::to still injects one when we pass `language` explicitly. Build
        // the default-language URL once and rewrite per language to keep
        // hreflang in sync with what the router actually serves.
        if ($urlParts === null) {
            $baseUrl = Url::current(['language' => null] + $currentParams, true);
        } else {
            $parts = is_array($urlParts) ? $urlParts : [$urlParts];
            $parts['language'] = null;
            $baseUrl = Url::to($parts, true);
        }

        foreach (self::SUPPORTED_LANGUAGES as $lang) {
            $result[$lang] = self::rewriteAbsoluteUrlLanguage($baseUrl, $lang);
        }

        $result['x-default'] = $result[self::DEFAULT_HREFLANG_LANGUAGE];

        return $result;
    }

    /**
     * Rewrite the language prefix in an absolute URL. Default language gets no
     * prefix; other languages get `/<lang>` inserted right after the host.
     */
    private static function rewriteAbsoluteUrlLanguage(string $absoluteUrl, string $language): string
    {
        $parsed = parse_url($absoluteUrl);
        if (!is_array($parsed) || !isset($parsed['scheme'], $parsed['host'])) {
            return $absoluteUrl;
        }

        $base = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $base .= ':' . $parsed['port'];
        }

        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';

        $pattern = '#^/(?:' . implode('|', self::SUPPORTED_LANGUAGES) . ')(?=/|$)#';
        $path = preg_replace($pattern, '', $path, 1) ?? $path;
        if ($path === '') {
            $path = '/';
        }

        if ($language !== self::DEFAULT_HREFLANG_LANGUAGE) {
            $path = '/' . $language . ($path === '/' ? '' : $path);
        }

        return $base . $path . $query;
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

        // Reserve 3 chars for the ellipsis so the final string never exceeds $limit.
        $budget = $limit - 3;
        $cut = mb_substr($value, 0, $budget + 1);
        $lastSpace = mb_strrpos($cut, ' ');
        if ($lastSpace !== false && $lastSpace >= (int)floor($budget * 0.6)) {
            $cut = mb_substr($cut, 0, $lastSpace);
        } else {
            $cut = mb_substr($cut, 0, $budget);
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
        return self::appendPageSuffix(T::tr('LEGO Sets On Sale — Best Deals on BrickAtlas'), $page);
    }

    public static function buildPromoDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO sets currently on sale across major retailers. Track price drops, exclusive promotions and the deepest discounts on BrickAtlas.');

        return self::truncate(self::appendPageDescriptionSuffix($description, $page));
    }

    public static function buildMagazinesTitle(int $page = 1): string
    {
        return self::appendPageSuffix(T::tr('LEGO Magazine Sets — Mini-Builds from Kiosks'), $page);
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
        return self::appendPageSuffix(T::tr('LEGO Exclusive Sets — LEGO.com Premiums on BrickAtlas'), $page);
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
        return self::appendPageSuffix(T::tr('LEGO Sets Retiring Soon — Catch Before They Go'), $page);
    }

    public static function buildRetiringSoonDescription(int $page = 1): string
    {
        $description = T::tr('Browse LEGO sets approaching their official retirement date. Catch sets before they leave shelves and resale prices climb on the aftermarket.');

        return self::truncate(self::appendPageDescriptionSuffix($description, $page));
    }

    public static function buildRetiringSoonIntro(): string
    {
        return T::tr('Sets with a confirmed retirement date — likely to disappear from retailers and climb in price once gone.');
    }

    public static function buildCatalogDescription(int $page = 1): string
    {
        $description = T::tr('Browse the LEGO sets catalog and filter by theme, subtheme, release year or piece count. Compare prices across major retailers on BrickAtlas.');

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

        // Long set names need truncation so the title fits in Google's ~60-char
        // SERP cutoff. The " (#####) — LEGO Set Prices | BrickAtlas" suffix
        // takes ~37 chars, leaving ~24 chars for the name.
        $maxNameLen = 24;
        if (mb_strlen($setName) > $maxNameLen) {
            $setName = rtrim(mb_substr($setName, 0, $maxNameLen - 1)) . '…';
        }

        return T::tr('{name} ({number}) — LEGO Set Prices | BrickAtlas', [
            'name'   => $setName,
            'number' => $setNumber,
        ]);
    }

    public static function buildSetDescription(Set $set): string
    {
        $brandTail = T::tr(' Track price drops on BrickAtlas.');

        $description = self::normalizeText($set->description);
        if ($description !== '') {
            return self::truncate($description . $brandTail);
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

        return self::truncate($summary . $brandTail);
    }

    /**
     * Slug whitelist for the audience/piece-count landing pages. The
     * controller uses this to look up the filter set; views use it for
     * titles, descriptions and hero copy. Centralised here so SEO copy and
     * route validation stay in lock-step.
     *
     * @return array<string, array{
     *     filters: array<string,int>,
     *     title: string,
     *     description: string,
     *     heroTitle: string,
     *     intro: string,
     *     icon: string,
     *     modifier: string,
     *     image: string,
     *     breadcrumb: string,
     * }>
     */
    public static function audiencePages(): array
    {
        return [
            'for-toddlers' => [
                'filters'     => ['age_max' => 4],
                'title'       => T::tr('LEGO Sets for Toddlers — Ages 1 to 4 on BrickAtlas'),
                'description' => T::tr('Browse LEGO Duplo sets and toddler-safe builds for ages 1 to 4. Compare prices, find big-piece sets and sensory toys on BrickAtlas.'),
                'heroTitle'   => T::tr('LEGO sets for toddlers'),
                'intro'       => T::tr('Duplo and toddler-safe builds for the smallest hands — large pieces, simple shapes, lots of color.'),
                'icon'        => 'bi-emoji-smile',
                'modifier'    => 'toddlers',
                'image'       => 'images/browse/toddlers.jpg',
                'breadcrumb'  => T::tr('For toddlers'),
            ],
            'for-kids' => [
                'filters'     => ['age_min' => 5, 'age_max' => 8],
                'title'       => T::tr('LEGO Sets for Kids — Ages 5 to 8 on BrickAtlas'),
                'description' => T::tr('Browse LEGO sets for kids aged 5 to 8. Compare prices and find approachable builds, fan-favourite themes and great gift ideas on BrickAtlas.'),
                'heroTitle'   => T::tr('LEGO sets for kids'),
                'intro'       => T::tr('Builds aimed at primary-school kids — confident enough for real bricks, simple enough to finish in one sitting.'),
                'icon'        => 'bi-emoji-laughing',
                'modifier'    => 'kids',
                'image'       => 'images/browse/kids.jpg',
                'breadcrumb'  => T::tr('For kids'),
            ],
            'for-tweens' => [
                'filters'     => ['age_min' => 9, 'age_max' => 12],
                'title'       => T::tr('LEGO Sets for Tweens — Ages 9 to 12 on BrickAtlas'),
                'description' => T::tr('Browse LEGO sets for tweens aged 9 to 12. Compare prices and discover more advanced builds, popular themes and gift-ready sets on BrickAtlas.'),
                'heroTitle'   => T::tr('LEGO sets for tweens'),
                'intro'       => T::tr('Bigger, more detailed builds for ages 9 to 12 — Star Wars vehicles, Friends towns, Ninjago dragons and more.'),
                'icon'        => 'bi-emoji-sunglasses',
                'modifier'    => 'tweens',
                'image'       => 'images/browse/tweens.jpg',
                'breadcrumb'  => T::tr('For tweens'),
            ],
            'for-teens' => [
                'filters'     => ['age_min' => 13],
                'title'       => T::tr('LEGO Sets for Teens — Ages 13 and Up on BrickAtlas'),
                'description' => T::tr('Browse LEGO sets for teens aged 13 and up. Compare prices and find advanced builds, display models and complex sets on BrickAtlas.'),
                'heroTitle'   => T::tr('LEGO sets for teens'),
                'intro'       => T::tr('Detail-heavy builds, longer build times and serious display models — sets that work for teens and adult builders alike.'),
                'icon'        => 'bi-headset',
                'modifier'    => 'teens',
                'image'       => 'images/browse/teens.jpg',
                'breadcrumb'  => T::tr('For teens'),
            ],
            'for-adults' => [
                'filters'     => ['age_min' => 18],
                'orTagSlug'   => '18-plus',
                'title'       => T::tr('LEGO Sets for Adults — 18+ Builder Collection on BrickAtlas'),
                'description' => T::tr('Browse the LEGO 18+ adult collection on BrickAtlas. Compare prices on Icons, Architecture, Botanical Collection and other display-grade sets.'),
                'heroTitle'   => T::tr('LEGO sets for adults'),
                'intro'       => T::tr('LEGO sets aimed at the 18+ builder — Icons, Architecture, Botanical Collection, Technic supercars and other display-grade builds.'),
                'icon'        => 'bi-person',
                'modifier'    => 'adults',
                'image'       => 'images/browse/adults.jpg',
                'breadcrumb'  => T::tr('For adults'),
            ],
            'small-builds' => [
                'filters'     => ['pieces_max' => 200],
                'title'       => T::tr('Small LEGO Builds — Sets Under 200 Pieces on BrickAtlas'),
                'description' => T::tr('Browse LEGO sets with fewer than 200 pieces. Compare prices on polybags, mini-builds and quick gift ideas across major retailers on BrickAtlas.'),
                'heroTitle'   => T::tr('Small LEGO builds'),
                'intro'       => T::tr('Quick builds and pocket-money sets under 200 pieces — perfect as gifts, party favours or impulse pickups.'),
                'icon'        => 'bi-bricks',
                'modifier'    => 'small-builds',
                'image'       => 'images/browse/small-builds.jpg',
                'breadcrumb'  => T::tr('Small builds'),
            ],
            'big-builds' => [
                'filters'     => ['pieces_min' => 2000],
                'title'       => T::tr('Big LEGO Builds — Sets with 2000+ Pieces on BrickAtlas'),
                'description' => T::tr('Browse LEGO sets with 2000+ pieces. Compare prices on UCS Star Wars, Modular Buildings, Technic flagships and other massive builds on BrickAtlas.'),
                'heroTitle'   => T::tr('Big LEGO builds'),
                'intro'       => T::tr('Multi-day, multi-thousand-piece projects — UCS Star Wars, Modular Buildings, Technic flagships and other showcase sets.'),
                'icon'        => 'bi-building',
                'modifier'    => 'big-builds',
                'image'       => 'images/browse/big-builds.jpg',
                'breadcrumb'  => T::tr('Big builds'),
            ],
        ];
    }

    public static function buildAudienceTitle(string $slug, int $page = 1): string
    {
        $config = self::audiencePages()[$slug] ?? null;
        if ($config === null) {
            return self::buildCatalogTitle($page);
        }

        return self::appendPageSuffix($config['title'], $page);
    }

    public static function buildAudienceDescription(string $slug, int $page = 1): string
    {
        $config = self::audiencePages()[$slug] ?? null;
        if ($config === null) {
            return self::buildCatalogDescription($page);
        }

        return self::appendPageDescriptionSuffix(self::truncate($config['description']), $page);
    }

    public static function buildMinifigTitle(string $displayName, int $page = 1): string
    {
        $name = self::normalizeText($displayName);
        if (mb_strlen($name) > 30) {
            $name = rtrim(mb_substr($name, 0, 29)) . '…';
        }

        return self::appendPageSuffix(T::tr('LEGO Sets with Minifigure {name} | BrickAtlas', ['name' => $name]), $page);
    }

    public static function buildMinifigDescription(string $displayName, string $number, int $page = 1): string
    {
        $name = self::normalizeText($displayName);
        if (mb_strlen($name) > 30) {
            $name = rtrim(mb_substr($name, 0, 29)) . '…';
        }

        $description = self::truncate(T::tr('Browse LEGO sets featuring minifigure {name} ({number}). Compare offers and track every appearance of this character on BrickAtlas.', [
            'name'   => $name,
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
