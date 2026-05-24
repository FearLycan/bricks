<?php

namespace console\controllers;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetMinifig;
use common\models\SetTag;
use common\models\Tag;
use common\models\Theme;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use RuntimeException;
use XMLWriter;
use yii\console\Controller;
use yii\console\ExitCode;

class SitemapController extends Controller
{
    private const SITEMAP_DIRECTORY_ALIAS = '@frontend/web/sitemap';

    /**
     * Supported front-end locales. The first entry is the default language and is served
     * without a URL prefix; every other locale gets a `/<lang>/...` prefix. This must
     * mirror the `languages` setting in `frontend/config/main.php`.
     */
    private const LANGUAGES = ['en', 'pl', 'de', 'fr', 'es', 'it', 'ja', 'zh'];
    private const DEFAULT_LANGUAGE = 'en';

    /**
     * Google rejects sitemaps over 50,000 URLs or 50 MB uncompressed. Set
     * slugs are long (~1.2 KB per URL with 9 hreflang alternates) so 40k
     * lands at ~46 MB uncompressed — under both limits with margin to spare.
     */
    private const MAX_URLS_PER_CHUNK = 40000;

    private const CUSTOM_LINKS = [
        /*[
            'path' => '/contact',
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ],*/
    ];

    public ?string $baseUrl    = null;
    public string  $outputPath = '@frontend/web/sitemap.xml';

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'baseUrl',
            'outputPath',
        ]);
    }

    public function optionAliases(): array
    {
        return [
            'u' => 'baseUrl',
            'o' => 'outputPath',
        ];
    }

    public function actionGenerate(): int
    {
        $configuredBaseUrl = \Yii::$app->params['frontend.baseUrl'] ?? null;
        $baseUrl = $this->normalizeBaseUrl($this->baseUrl ?? (is_string($configuredBaseUrl) ? $configuredBaseUrl : null));
        if ($baseUrl === null) {
            $this->stderr("Missing base URL. Set params['frontend.baseUrl'] or pass --base-url=https://brickatlas.example\n");

            return ExitCode::USAGE;
        }

        $this->cleanupOldSitemaps();

        $indexEntries = [];
        $totalUrls = 0;

        $totalUrls += $this->writeChunkedSitemap('sitemap-static', $this->iterateStaticEntries($baseUrl), $indexEntries, $baseUrl);
        $totalUrls += $this->writeChunkedSitemap('sitemap-themes', $this->iterateThemeEntries($baseUrl), $indexEntries, $baseUrl);
        $totalUrls += $this->writeChunkedSitemap('sitemap-sets', $this->iterateSetEntries($baseUrl), $indexEntries, $baseUrl);
        $totalUrls += $this->writeChunkedSitemap('sitemap-minifigs', $this->iterateMinifigEntries($baseUrl), $indexEntries, $baseUrl);
        $totalUrls += $this->writeChunkedSitemap('sitemap-tags', $this->iterateTagEntries($baseUrl), $indexEntries, $baseUrl);

        $this->writeIndexSitemap($indexEntries);

        $this->stdout('Generated sitemap index with ' . count($indexEntries) . " files and {$totalUrls} URLs in {$this->outputPath}\n");

        return ExitCode::OK;
    }

    // ─── Entry source generators ────────────────────────────────────────────────
    //
    // Each generator yields one logical URL at a time. The chunked writer
    // expands a logical URL into 8 per-language `<url>` elements, so a
    // generator yielding N items produces N * 8 URLs in the sitemap.

    /**
     * @return Generator<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}>
     */
    private function iterateStaticEntries(string $baseUrl): Generator
    {
        $static = [
            ['/lego',                 'daily',   '1.0'],
            ['/lego/on-sale',         'daily',   '0.7'],
            ['/lego/magazines',       'weekly',  '0.6'],
            ['/lego/exclusive',       'weekly',  '0.7'],
            ['/lego/retiring-soon',   'daily',   '0.7'],
            ['/lego/for-toddlers',    'weekly',  '0.6'],
            ['/lego/for-kids',        'weekly',  '0.6'],
            ['/lego/for-tweens',      'weekly',  '0.6'],
            ['/lego/for-teens',       'weekly',  '0.6'],
            ['/lego/for-adults',      'weekly',  '0.7'],
            ['/lego/small-builds',    'weekly',  '0.6'],
            ['/lego/big-builds',      'weekly',  '0.6'],
            ['/glossary',             'monthly', '0.5'],
            ['/interests',            'weekly',  '0.7'],
            ['/faq',                  'monthly', '0.5'],
        ];

        foreach ($static as [$path, $changefreq, $priority]) {
            yield [
                'loc'        => $this->buildAbsoluteUrl($baseUrl, $path),
                'lastmod'    => null,
                'changefreq' => $changefreq,
                'priority'   => $priority,
            ];
        }

        foreach (self::CUSTOM_LINKS as $link) {
            $path = (string)($link['path'] ?? '');
            if ($path === '') {
                continue;
            }

            yield [
                'loc'        => $this->buildAbsoluteUrl($baseUrl, $path),
                'lastmod'    => null,
                'changefreq' => (string)($link['changefreq'] ?? 'monthly'),
                'priority'   => (string)($link['priority'] ?? '0.5'),
            ];
        }
    }

    private function iterateThemeEntries(string $baseUrl): Generator
    {
        $mainThemesQuery = Theme::find()
            ->select(['id', 'slug', 'updated_at', 'created_at'])
            ->where(['status' => StatusEnum::ACTIVE->value, 'parent_id' => null])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->asArray();

        $mainThemeById = [];
        foreach ($mainThemesQuery->each() as $theme) {
            $mainThemeById[(int)$theme['id']] = (string)$theme['slug'];

            yield [
                'loc'        => $this->buildAbsoluteUrl($baseUrl, '/lego/theme/' . rawurlencode((string)$theme['slug'])),
                'lastmod'    => $this->resolveLastModified($theme['updated_at'] ?? null, $theme['created_at'] ?? null),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];
        }

        if ($mainThemeById === []) {
            return;
        }

        $subThemesQuery = Theme::find()
            ->select(['id', 'slug', 'parent_id', 'updated_at', 'created_at'])
            ->where(['status' => StatusEnum::ACTIVE->value])
            ->andWhere(['in', 'parent_id', array_keys($mainThemeById)])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->asArray();

        foreach ($subThemesQuery->each() as $subTheme) {
            $parentSlug = $mainThemeById[(int)$subTheme['parent_id']] ?? null;
            if ($parentSlug === null) {
                continue;
            }

            yield [
                'loc'        => $this->buildAbsoluteUrl($baseUrl, '/lego/theme/' . rawurlencode($parentSlug) . '/' . rawurlencode((string)$subTheme['slug'])),
                'lastmod'    => $this->resolveLastModified($subTheme['updated_at'] ?? null, $subTheme['created_at'] ?? null),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }
    }

    private function iterateSetEntries(string $baseUrl): Generator
    {
        $query = Set::find()
            ->select(['slug', 'updated_at', 'created_at'])
            ->where(['status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->asArray();

        foreach ($query->batch(1000) as $rows) {
            foreach ($rows as $row) {
                yield [
                    'loc'        => $this->buildAbsoluteUrl($baseUrl, '/lego/' . rawurlencode((string)$row['slug'])),
                    'lastmod'    => $this->resolveLastModified($row['updated_at'] ?? null, $row['created_at'] ?? null),
                    'changefreq' => 'weekly',
                    'priority'   => '0.9',
                ];
            }
        }
    }

    private function iterateMinifigEntries(string $baseUrl): Generator
    {
        $query = SetMinifig::find()
            ->alias('sm')
            ->select(['sm.number', 'max(sm.updated_at) AS updated_at', 'max(sm.created_at) AS created_at'])
            ->innerJoin(Set::tableName() . ' s', 's.id = sm.set_id')
            ->where(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['sm.number' => null]])
            ->andWhere(['<>', 'sm.number', ''])
            ->groupBy(['sm.number'])
            ->asArray();

        foreach ($query->each() as $row) {
            yield [
                'loc'        => $this->buildAbsoluteUrl($baseUrl, '/lego/minifig/' . rawurlencode((string)$row['number'])),
                'lastmod'    => $this->resolveLastModified($row['updated_at'] ?? null, $row['created_at'] ?? null),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        }
    }

    private function iterateTagEntries(string $baseUrl): Generator
    {
        $query = Tag::find()
            ->alias('t')
            ->select(['t.slug', 'MAX(t.updated_at) AS updated_at', 'MAX(t.created_at) AS created_at'])
            ->innerJoin(SetTag::tableName() . ' st', 'st.tag_id = t.id')
            ->innerJoin(Set::tableName() . ' s', 's.id = st.set_id AND s.status = :status', [':status' => StatusEnum::ACTIVE->value])
            ->where(['t.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['t.slug' => null]])
            ->andWhere(['<>', 't.slug', ''])
            ->groupBy(['t.id', 't.slug'])
            ->asArray();

        foreach ($query->each() as $tag) {
            yield [
                'loc'        => $this->buildAbsoluteUrl($baseUrl, '/lego/tag/' . rawurlencode((string)$tag['slug'])),
                'lastmod'    => $this->resolveLastModified($tag['updated_at'] ?? null, $tag['created_at'] ?? null),
                'changefreq' => 'weekly',
                'priority'   => '0.5',
            ];
        }
    }

    // ─── Streaming writer ──────────────────────────────────────────────────────

    /**
     * Stream each yielded entry into one or more gzipped sitemap files,
     * rotating to a new chunk before the URL count crosses Google's limit.
     *
     * @param iterable<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}> $entries
     */
    private function writeChunkedSitemap(string $baseName, iterable $entries, array &$indexEntries, string $baseUrl): int
    {
        $totalUrls = 0;
        $urlsInChunk = 0;
        $chunkIndex = 1;
        $writer = null;
        $currentFilePath = null;

        foreach ($entries as $entry) {
            if ($writer === null) {
                $currentFilePath = $this->resolveChunkPath($baseName, $chunkIndex);
                $writer = $this->openSitemapWriter($currentFilePath);
            }

            $written = $this->writeUrlElement($writer, $entry['loc'], $entry['lastmod'] ?? null, $entry['changefreq'], $entry['priority']);
            $urlsInChunk += $written;
            $totalUrls += $written;

            if ($urlsInChunk >= self::MAX_URLS_PER_CHUNK) {
                $this->closeSitemapWriter($writer, $currentFilePath, $indexEntries, $baseUrl);
                $writer = null;
                $urlsInChunk = 0;
                $chunkIndex++;
            }
        }

        if ($writer !== null) {
            $this->closeSitemapWriter($writer, $currentFilePath, $indexEntries, $baseUrl);
        }

        return $totalUrls;
    }

    /**
     * If a base name produced a single chunk, write it as the base file name
     * (e.g. `sitemap-static.xml.gz`). Otherwise number chunks from 1.
     */
    private function resolveChunkPath(string $baseName, int $chunkIndex): string
    {
        // Always use numbered chunks for predictability — even single-chunk
        // sub-sitemaps get `-1` to keep the index format consistent and avoid
        // collisions if a category later grows past one chunk.
        $fileName = $baseName . '-' . $chunkIndex . '.xml.gz';

        return $this->buildOutputFilePath($fileName);
    }

    private function openSitemapWriter(string $filePath): XMLWriter
    {
        $directory = dirname($filePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create sitemap directory: {$directory}");
        }

        $writer = new XMLWriter();
        // The compress.zlib:// stream wrapper makes XMLWriter pipe directly
        // into a gzip-compressed file — no in-memory buffer needed.
        if (!$writer->openUri('compress.zlib://' . $filePath)) {
            throw new RuntimeException("Failed to open sitemap writer for {$filePath}");
        }

        $writer->setIndent(true);
        $writer->setIndentString(' ');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $writer->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        return $writer;
    }

    private function closeSitemapWriter(XMLWriter $writer, string $filePath, array &$indexEntries, string $baseUrl): void
    {
        $writer->endElement(); // urlset
        $writer->endDocument();
        $writer->flush();

        $indexEntries[] = [
            'loc'     => $this->buildSitemapFileUrl($filePath, $baseUrl),
            'lastmod' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }

    /**
     * Write one logical URL as 8 per-language `<url>` elements, each carrying
     * the full set of `xhtml:link rel="alternate"` for SEO. Returns the count
     * of `<url>` elements actually written.
     */
    private function writeUrlElement(XMLWriter $writer, string $loc, ?string $lastmod, string $changefreq, string $priority): int
    {
        $path = $this->extractPath($loc);
        $alternates = $this->buildLanguageAlternates($loc, $path);

        $written = 0;
        foreach (self::LANGUAGES as $language) {
            $writer->startElement('url');
            $writer->writeElement('loc', $alternates[$language]);
            if ($lastmod !== null) {
                $writer->writeElement('lastmod', $lastmod);
            }
            $writer->writeElement('changefreq', $changefreq);
            $writer->writeElement('priority', $priority);

            foreach ($alternates as $hreflang => $href) {
                $writer->startElement('xhtml:link');
                $writer->writeAttribute('rel', 'alternate');
                $writer->writeAttribute('hreflang', (string)$hreflang);
                $writer->writeAttribute('href', (string)$href);
                $writer->endElement();
            }

            $writer->endElement(); // url
            $written++;
        }

        return $written;
    }

    private function writeIndexSitemap(array $entries): void
    {
        $path = \Yii::getAlias($this->outputPath);
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create sitemap directory: {$directory}");
        }

        $writer = new XMLWriter();
        if (!$writer->openUri($path)) {
            throw new RuntimeException("Failed to open sitemap index writer for {$path}");
        }

        $writer->setIndent(true);
        $writer->setIndentString(' ');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('sitemapindex');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($entries as $entry) {
            $writer->startElement('sitemap');
            $writer->writeElement('loc', $entry['loc']);
            $writer->writeElement('lastmod', $entry['lastmod']);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
    }

    /**
     * Remove old chunked sitemap files before generating fresh ones so that
     * shrinking categories don't leave stale chunks pointing at gone content.
     */
    private function cleanupOldSitemaps(): void
    {
        $directory = \Yii::getAlias(self::SITEMAP_DIRECTORY_ALIAS);
        if (!is_dir($directory)) {
            return;
        }

        $patterns = [
            $directory . DIRECTORY_SEPARATOR . 'sitemap-*.xml',
            $directory . DIRECTORY_SEPARATOR . 'sitemap-*.xml.gz',
        ];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                @unlink($file);
            }
        }
    }

    // ─── URL / path helpers ────────────────────────────────────────────────────

    private function extractPath(string $loc): string
    {
        $parts = parse_url($loc);
        if ($parts === false) {
            return '/';
        }

        $path = $parts['path'] ?? '/';
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        if (isset($parts['query']) && $parts['query'] !== '') {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }

    /**
     * @return array<string,string>
     */
    private function buildLanguageAlternates(string $absoluteUrl, string $path): array
    {
        $baseUrl = $this->stripPath($absoluteUrl, $path);
        $alternates = [];
        foreach (self::LANGUAGES as $language) {
            $alternates[$language] = $baseUrl . $this->prefixLanguage($path, $language);
        }
        $alternates['x-default'] = $alternates[self::DEFAULT_LANGUAGE];

        return $alternates;
    }

    private function stripPath(string $absoluteUrl, string $path): string
    {
        if ($path === '' || $path === '/') {
            return rtrim($absoluteUrl, '/');
        }

        if (str_ends_with($absoluteUrl, $path)) {
            return substr($absoluteUrl, 0, -strlen($path));
        }

        $parsed = parse_url($absoluteUrl);
        if (!is_array($parsed) || !isset($parsed['scheme'], $parsed['host'])) {
            return rtrim($absoluteUrl, '/');
        }

        $base = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $base .= ':' . $parsed['port'];
        }

        return $base;
    }

    private function prefixLanguage(string $path, string $language): string
    {
        if ($language === self::DEFAULT_LANGUAGE) {
            return $path;
        }

        if ($path === '' || $path === '/') {
            return '/' . $language;
        }

        return '/' . $language . $path;
    }

    private function buildOutputFilePath(string $fileName): string
    {
        $sitemapDirectory = \Yii::getAlias(self::SITEMAP_DIRECTORY_ALIAS);

        return rtrim($sitemapDirectory, '/\\') . DIRECTORY_SEPARATOR . $fileName;
    }

    private function buildSitemapFileUrl(string $filePath, string $baseUrl): string
    {
        $frontendWebPath = \Yii::getAlias('@frontend/web');
        $normalizedWebPath = str_replace('\\', '/', rtrim($frontendWebPath, '/\\'));
        $normalizedFilePath = str_replace('\\', '/', $filePath);

        if (!str_starts_with($normalizedFilePath, $normalizedWebPath)) {
            throw new RuntimeException("Sitemap file must be inside @frontend/web: {$filePath}");
        }

        $relativePath = ltrim(substr($normalizedFilePath, strlen($normalizedWebPath)), '/');

        return $this->buildAbsoluteUrl($baseUrl, '/' . $relativePath);
    }

    private function buildAbsoluteUrl(string $baseUrl, string $path): string
    {
        $normalizedPath = '/' . ltrim($path, '/');

        return rtrim($baseUrl, '/') . $normalizedPath;
    }

    private function resolveLastModified(mixed $updatedAt, mixed $createdAt): ?string
    {
        $updated = $this->toSitemapDate($updatedAt);
        if ($updated !== null) {
            return $updated;
        }

        return $this->toSitemapDate($createdAt);
    }

    private function toSitemapDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format(DateTimeInterface::ATOM);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeBaseUrl(?string $baseUrl): ?string
    {
        if ($baseUrl === null) {
            return null;
        }

        $baseUrl = trim($baseUrl);
        if ($baseUrl === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $baseUrl) !== 1) {
            return null;
        }

        return rtrim($baseUrl, '/');
    }
}
