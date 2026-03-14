<?php

namespace console\controllers;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetMinifig;
use common\models\Theme;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use yii\console\Controller;
use yii\console\ExitCode;

class SitemapController extends Controller
{
    private const SITEMAP_DIRECTORY_ALIAS = '@frontend/web/sitemap';

    private const CUSTOM_LINKS = [
        /*[
            'path' => '/contact',
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ],
        [
            'path' => '/terms',
            'changefreq' => 'yearly',
            'priority' => '0.3',
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

        $staticEntries = [];
        $this->addEntry($staticEntries, $this->buildAbsoluteUrl($baseUrl, '/lego'), null, 'daily', '1.0');
        $this->appendCustomEntries($staticEntries, $baseUrl);

        $themeEntries = [];
        $this->appendThemeEntries($themeEntries, $baseUrl);

        $setEntries = [];
        $this->appendSetEntries($setEntries, $baseUrl);

        $minifigEntries = [];
        $this->appendMinifigEntries($minifigEntries, $baseUrl);

        $sitemapFiles = [
            'sitemap-static.xml'   => $staticEntries,
            'sitemap-themes.xml'   => $themeEntries,
            'sitemap-sets.xml'     => $setEntries,
            'sitemap-minifigs.xml' => $minifigEntries,
        ];

        $indexEntries = [];
        $totalUrls = 0;
        foreach ($sitemapFiles as $fileName => $entries) {
            if ($entries === []) {
                continue;
            }

            usort($entries, static fn(array $a, array $b): int => strcmp($a['loc'], $b['loc']));
            $xml = $this->buildUrlSetXml($entries);
            $absolutePath = $this->buildOutputFilePath($fileName);
            $this->writeFile($xml, $absolutePath);
            $indexEntries[] = [
                'loc'     => $this->buildSitemapFileUrl($absolutePath, $baseUrl),
                'lastmod' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            ];
            $totalUrls += count($entries);
        }

        $indexXml = $this->buildSitemapIndexXml($indexEntries);
        $this->writeFile($indexXml, \Yii::getAlias($this->outputPath));

        $this->stdout('Generated sitemap index with ' . count($indexEntries) . " files and {$totalUrls} URLs in {$this->outputPath}\n");

        return ExitCode::OK;
    }

    private function appendThemeEntries(array &$entries, string $baseUrl): void
    {
        $activeMainThemes = Theme::find()
            ->select(['id', 'slug', 'updated_at', 'created_at'])
            ->where([
                'status'    => StatusEnum::ACTIVE->value,
                'parent_id' => null,
            ])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->asArray()
            ->all();

        $mainThemeById = [];
        foreach ($activeMainThemes as $theme) {
            $themeId = (int)$theme['id'];
            $slug = (string)$theme['slug'];
            $mainThemeById[$themeId] = $slug;

            $this->addEntry(
                $entries,
                $this->buildAbsoluteUrl($baseUrl, '/lego/theme/' . rawurlencode($slug)),
                $this->resolveLastModified($theme['updated_at'] ?? null, $theme['created_at'] ?? null),
                'weekly',
                '0.8'
            );
        }

        if ($mainThemeById === []) {
            return;
        }

        $activeSubThemes = Theme::find()
            ->select(['id', 'slug', 'parent_id', 'updated_at', 'created_at'])
            ->where([
                'status' => StatusEnum::ACTIVE->value,
            ])
            ->andWhere(['in', 'parent_id', array_keys($mainThemeById)])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->asArray()
            ->all();

        foreach ($activeSubThemes as $subTheme) {
            $parentId = (int)$subTheme['parent_id'];
            $parentSlug = $mainThemeById[$parentId] ?? null;
            if ($parentSlug === null) {
                continue;
            }

            $slug = (string)$subTheme['slug'];
            $this->addEntry(
                $entries,
                $this->buildAbsoluteUrl($baseUrl, '/lego/theme/' . rawurlencode($parentSlug) . '/' . rawurlencode($slug)),
                $this->resolveLastModified($subTheme['updated_at'] ?? null, $subTheme['created_at'] ?? null),
                'weekly',
                '0.7'
            );
        }
    }

    private function appendSetEntries(array &$entries, string $baseUrl): void
    {
        $query = Set::find()
            ->select(['slug', 'updated_at', 'created_at'])
            ->where(['status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->asArray();

        foreach ($query->batch(1000) as $rows) {
            foreach ($rows as $row) {
                $slug = (string)$row['slug'];
                $this->addEntry(
                    $entries,
                    $this->buildAbsoluteUrl($baseUrl, '/lego/' . rawurlencode($slug)),
                    $this->resolveLastModified($row['updated_at'] ?? null, $row['created_at'] ?? null),
                    'weekly',
                    '0.9'
                );
            }
        }
    }

    private function appendMinifigEntries(array &$entries, string $baseUrl): void
    {
        $rows = SetMinifig::find()
            ->alias('sm')
            ->select(['sm.number', 'max(sm.updated_at) AS updated_at', 'max(sm.created_at) AS created_at'])
            ->innerJoin(Set::tableName() . ' s', 's.id = sm.set_id')
            ->where(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['sm.number' => null]])
            ->andWhere(['<>', 'sm.number', ''])
            ->groupBy(['sm.number'])
            ->asArray()
            ->all();

        foreach ($rows as $row) {
            $number = (string)$row['number'];
            $this->addEntry(
                $entries,
                $this->buildAbsoluteUrl($baseUrl, '/lego/minifig/' . rawurlencode($number)),
                $this->resolveLastModified($row['updated_at'] ?? null, $row['created_at'] ?? null),
                'weekly',
                '0.6'
            );
        }
    }

    private function appendCustomEntries(array &$entries, string $baseUrl): void
    {
        foreach (self::CUSTOM_LINKS as $link) {
            $path = (string)($link['path'] ?? '');
            if ($path === '') {
                continue;
            }

            $changefreq = (string)($link['changefreq'] ?? 'monthly');
            $priority = (string)($link['priority'] ?? '0.5');
            $this->addEntry($entries, $this->buildAbsoluteUrl($baseUrl, $path), null, $changefreq, $priority);
        }
    }

    private function addEntry(array &$entries, string $loc, ?string $lastmod, string $changefreq, string $priority): void
    {
        $entry = [
            'loc'        => $loc,
            'changefreq' => $changefreq,
            'priority'   => $priority,
        ];

        if ($lastmod !== null) {
            $entry['lastmod'] = $lastmod;
        }

        $entries[] = $entry;
    }

    private function buildUrlSetXml(array $entries): string
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);

        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($entries as $entry) {
            $writer->startElement('url');
            $writer->writeElement('loc', $entry['loc']);

            if (isset($entry['lastmod'])) {
                $writer->writeElement('lastmod', $entry['lastmod']);
            }

            $writer->writeElement('changefreq', $entry['changefreq']);
            $writer->writeElement('priority', $entry['priority']);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    private function buildSitemapIndexXml(array $entries): string
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);

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

        return $writer->outputMemory();
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

    private function writeFile(string $xml, string $path): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException("Failed to create output directory: {$directory}");
            }
        }

        $written = file_put_contents($path, $xml, LOCK_EX);
        if ($written === false) {
            throw new RuntimeException("Failed to write sitemap to {$path}");
        }
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
