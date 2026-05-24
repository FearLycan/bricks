<?php

namespace frontend\components;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetInstruction;
use common\models\SetOffer;
use DateTimeImmutable;
use DateTimeInterface;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * Builds the JSON Feed 1.1 payload published at /feed and consumed by the
 * Atom renderer at /feed.xml.
 *
 * Each item is one catalog event. Event types and their sources:
 *   - new_set         → `set.created_at` desc, one event per set
 *   - new_offer       → `set_offer.created_at` desc, one event per offer
 *                       (substitute for "price change" — we don't track price history)
 *   - new_instruction → grouped per set (one event covers all PDFs published for the
 *                       set; the list itself is rendered into `content_html`)
 *   - retiring_soon   → `set.exit_date` in [today, +90d]
 *
 * The four streams are merged and the top {@see self::MAX_ITEMS} most recent items
 * are returned. Title/content_text are translated via {@see T::tr()} so the same
 * service powers /feed (EN) and /pl/feed (PL), and the Atom rendering inherits
 * the same payload.
 */
final class FeedService
{
    public const MAX_ITEMS = 50;
    public const CACHE_TTL = 300;

    private const RETIRING_WINDOW_DAYS = 90;
    private const AUTHOR_NAME = 'BrickAtlas Bot';

    public function buildFeed(): array
    {
        $language = (string)Yii::$app->language;
        $cacheKey = 'feed.json.' . $language;

        return Yii::$app->cache->getOrSet($cacheKey, function () {
            $items = $this->buildItems();

            return [
                'version'       => 'https://jsonfeed.org/version/1.1',
                'title'         => T::tr('BrickAtlas — LEGO catalog updates'),
                'description'   => T::tr('Latest catalog activity on BrickAtlas: new sets, fresh store offers, published PDF instructions and sets approaching retirement.'),
                'home_page_url' => Url::home(true),
                'feed_url'      => Url::to(['/feed'], true),
                'language'      => (string)Yii::$app->language,
                'authors'       => [$this->feedAuthor()],
                'items'         => $items,
            ];
        }, self::CACHE_TTL);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(): array
    {
        $items = array_merge(
            $this->buildNewSetItems(),
            $this->buildNewOfferItems(),
            $this->buildNewInstructionItems(),
            $this->buildRetiringItems(),
        );

        usort($items, static fn(array $a, array $b): int => strcmp($b['date_published'], $a['date_published']));

        return array_slice($items, 0, self::MAX_ITEMS);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildNewSetItems(): array
    {
        $sets = Set::find()
            ->alias('s')
            ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['s.slug' => null]])
            ->andWhere(['<>', 's.slug', ''])
            ->with(['theme'])
            ->orderBy(['s.created_at' => SORT_DESC, 's.id' => SORT_DESC])
            ->limit(self::MAX_ITEMS)
            ->all();

        $items = [];
        foreach ($sets as $set) {
            $publishedAt = $this->toIsoDate($set->created_at);
            if ($publishedAt === null) {
                continue;
            }

            $setUrl = $this->setUrl($set);
            $image = $this->setImage($set);
            $title = T::tr('New set: {number} {name}', [
                'number' => $set->getSetNumberText(),
                'name'   => (string)$set->name,
            ]);
            $text = $this->buildNewSetContent($set);

            $items[] = [
                'id'             => 'set-new-' . (int)$set->id,
                'url'            => $setUrl,
                'title'          => $title,
                'content_text'   => $text,
                'content_html'   => $this->buildContentHtml($text, $setUrl, $title, $image),
                'image'          => $image,
                'date_published' => $publishedAt,
                'authors'        => [$this->feedAuthor()],
                'tags'           => $this->buildTags('new-set', $set),
                '_kind'          => 'new_set',
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildNewOfferItems(): array
    {
        $offers = SetOffer::find()
            ->alias('o')
            ->innerJoin(['s' => Set::tableName()], 's.id = o.set_id AND s.status = ' . StatusEnum::ACTIVE->value)
            ->andWhere(['not', ['s.slug' => null]])
            ->andWhere(['<>', 's.slug', ''])
            ->andWhere(['not', ['o.price' => null]])
            ->with(['set.theme', 'store'])
            ->orderBy(['o.created_at' => SORT_DESC, 'o.id' => SORT_DESC])
            ->limit(self::MAX_ITEMS)
            ->all();

        $items = [];
        foreach ($offers as $offer) {
            $set = $offer->set;
            if ($set === null) {
                continue;
            }

            $publishedAt = $this->toIsoDate($offer->created_at);
            if ($publishedAt === null) {
                continue;
            }

            $setUrl = $this->setUrl($set);
            $image = $this->setImage($set);
            $price = $offer->getFormattedPriceOrDefault('-');
            $storeName = (string)($offer->store->name ?? T::tr('a store'));
            $title = T::tr('New offer: {number} {name}', [
                'number' => $set->getSetNumberText(),
                'name'   => (string)$set->name,
            ]);
            $text = T::tr('New offer for {name} ({number}): {price} at {store}.', [
                'name'   => (string)$set->name,
                'number' => $set->getSetNumberText(),
                'price'  => $price,
                'store'  => $storeName,
            ]);

            $items[] = [
                'id'             => 'offer-new-' . (int)$offer->id,
                'url'            => $setUrl,
                'title'          => $title,
                'content_text'   => $text,
                'content_html'   => $this->buildOfferContentHtml($text, $setUrl, $title, $image, $offer, $price, $storeName),
                'image'          => $image,
                'date_published' => $publishedAt,
                'authors'        => [$this->feedAuthor()],
                'tags'           => array_merge($this->buildTags('new-offer', $set), ['store-' . Inflector::slug($storeName, '-')]),
                '_kind'          => 'new_offer',
            ];
        }

        return $items;
    }

    /**
     * Group instruction events per set: one feed item covers every PDF a set has
     * accumulated, surfaced by the most recent upload. Avoids spamming the feed
     * with 17-21 entries when a multi-instruction set (e.g. Bionicle) is imported.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildNewInstructionItems(): array
    {
        // Two-step: aggregation produces raw set_id → (count, last_at) rows, then we
        // load the matching Sets with eager-loaded relations for rendering. Avoids
        // ActiveRecord's strict property model for ad-hoc SELECT aliases.
        $aggregates = (new Query())
            ->select([
                'set_id',
                'last_at' => new Expression('MAX(created_at)'),
                'cnt'     => new Expression('COUNT(id)'),
            ])
            ->from(SetInstruction::tableName())
            ->groupBy(['set_id'])
            ->orderBy(['last_at' => SORT_DESC])
            ->limit(self::MAX_ITEMS)
            ->all();

        if ($aggregates === []) {
            return [];
        }

        $setIds = array_column($aggregates, 'set_id');
        $sets = Set::find()
            ->where(['id' => $setIds, 'status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['<>', 'slug', ''])
            ->with(['theme', 'setInstructions'])
            ->indexBy('id')
            ->all();

        $items = [];
        foreach ($aggregates as $row) {
            $set = $sets[(int)$row['set_id']] ?? null;
            if ($set === null) {
                continue;
            }

            $publishedAt = $this->toIsoDate((string)$row['last_at']);
            if ($publishedAt === null) {
                continue;
            }

            $count = (int)$row['cnt'];
            $setUrl = $this->setUrl($set);
            $image = $this->setImage($set);

            $title = $count === 1
                ? T::tr('New PDF instruction: {number} {name}', [
                    'number' => $set->getSetNumberText(),
                    'name'   => (string)$set->name,
                ])
                : T::tr('New PDF instructions ({n}): {number} {name}', [
                    'n'      => $count,
                    'number' => $set->getSetNumberText(),
                    'name'   => (string)$set->name,
                ]);

            $text = $count === 1
                ? T::tr('Building instruction PDF published for {name} ({number}).', [
                    'name'   => (string)$set->name,
                    'number' => $set->getSetNumberText(),
                ])
                : T::tr('{n} building instruction PDFs available for {name} ({number}).', [
                    'n'      => $count,
                    'name'   => (string)$set->name,
                    'number' => $set->getSetNumberText(),
                ]);

            $items[] = [
                'id'             => 'instructions-set-' . (int)$set->id,
                'url'            => $setUrl,
                'title'          => $title,
                'content_text'   => $text,
                'content_html'   => $this->buildInstructionContentHtml($text, $setUrl, $title, $image, $set->setInstructions),
                'image'          => $image,
                'date_published' => $publishedAt,
                'authors'        => [$this->feedAuthor()],
                'tags'           => $this->buildTags('instruction', $set),
                '_kind'          => 'new_instruction',
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRetiringItems(): array
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $windowEnd = (new DateTimeImmutable('today + ' . self::RETIRING_WINDOW_DAYS . ' days'))->format('Y-m-d');

        $sets = Set::find()
            ->alias('s')
            ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['s.slug' => null]])
            ->andWhere(['<>', 's.slug', ''])
            ->andWhere(['not', ['s.exit_date' => null]])
            ->andWhere(['between', 's.exit_date', $today, $windowEnd])
            ->with(['theme'])
            ->orderBy(['s.exit_date' => SORT_ASC, 's.id' => SORT_ASC])
            ->limit(self::MAX_ITEMS)
            ->all();

        $items = [];
        foreach ($sets as $set) {
            $exitDate = $this->toIsoDate((string)$set->exit_date);
            if ($exitDate === null) {
                continue;
            }

            $publishedAt = $this->toIsoDate($set->updated_at ?? $set->created_at) ?? $exitDate;
            $setUrl = $this->setUrl($set);
            $image = $this->setImage($set);
            $exitDay = substr((string)$set->exit_date, 0, 10);
            $title = T::tr('Retiring soon: {number} {name}', [
                'number' => $set->getSetNumberText(),
                'name'   => (string)$set->name,
            ]);
            $text = T::tr('{name} ({number}) is scheduled to retire on {date}.', [
                'name'   => (string)$set->name,
                'number' => $set->getSetNumberText(),
                'date'   => $exitDay,
            ]);

            $items[] = [
                'id'             => 'set-retiring-' . (int)$set->id . '-' . $exitDay,
                'url'            => $setUrl,
                'title'          => $title,
                'content_text'   => $text,
                'content_html'   => $this->buildRetiringContentHtml($text, $setUrl, $title, $image, $exitDay),
                'image'          => $image,
                'date_published' => $publishedAt,
                'authors'        => [$this->feedAuthor()],
                'tags'           => $this->buildTags('retiring-soon', $set),
                '_kind'          => 'retiring_soon',
            ];
        }

        return $items;
    }

    private function buildNewSetContent(Set $set): string
    {
        $parts = [];
        $themeName = (string)($set->theme->name ?? '');
        if ($themeName !== '') {
            $parts[] = T::tr('Theme: {theme}', ['theme' => $themeName]);
        }
        if ($set->pieces) {
            $parts[] = T::tr('{n} pieces', ['n' => (int)$set->pieces]);
        }
        if ($set->year) {
            $parts[] = T::tr('Released {year}', ['year' => (int)$set->year]);
        }

        $summary = T::tr('New LEGO set added to the catalog: {name} ({number}).', [
            'name'   => (string)$set->name,
            'number' => $set->getSetNumberText(),
        ]);

        if ($parts !== []) {
            $summary .= ' ' . implode(', ', $parts) . '.';
        }

        return $summary;
    }

    // ─── content_html builders ────────────────────────────────────────────────

    private function buildContentHtml(string $text, string $url, string $title, ?string $image): string
    {
        $html = '<p>' . Html::encode($text) . '</p>';
        if ($image !== null) {
            $html .= '<p>' . Html::img($image, ['alt' => $title, 'style' => 'max-width:480px;height:auto;']) . '</p>';
        }
        $html .= '<p>' . Html::a(Html::encode(T::tr('View on BrickAtlas')) . ' →', $url) . '</p>';

        return $html;
    }

    private function buildOfferContentHtml(string $text, string $url, string $title, ?string $image, SetOffer $offer, string $price, string $storeName): string
    {
        $html = '<p>' . Html::encode($text) . '</p>';
        if ($image !== null) {
            $html .= '<p>' . Html::img($image, ['alt' => $title, 'style' => 'max-width:480px;height:auto;']) . '</p>';
        }
        $html .= '<p><strong>' . Html::encode($price) . '</strong> · ' . Html::encode($storeName) . '</p>';
        $html .= '<p>' . Html::a(Html::encode(T::tr('View set on BrickAtlas')), $url);
        if (!empty($offer->url)) {
            $html .= ' · ' . Html::a(Html::encode(T::tr('Go to offer')) . ' →', $offer->url, ['rel' => 'nofollow noopener']);
        }
        $html .= '</p>';

        return $html;
    }

    /**
     * @param SetInstruction[] $instructions
     */
    private function buildInstructionContentHtml(string $text, string $url, string $title, ?string $image, array $instructions): string
    {
        $html = '<p>' . Html::encode($text) . '</p>';
        if ($image !== null) {
            $html .= '<p>' . Html::img($image, ['alt' => $title, 'style' => 'max-width:480px;height:auto;']) . '</p>';
        }

        if ($instructions !== []) {
            $html .= '<ul>';
            foreach ($instructions as $i => $instruction) {
                $pdfUrl = trim((string)$instruction->url);
                if ($pdfUrl === '') {
                    continue;
                }
                $label = trim((string)($instruction->description ?? ''));
                if ($label === '') {
                    $label = T::tr('Instruction {n}', ['n' => $i + 1]);
                }
                $html .= '<li>' . Html::a(Html::encode($label), $pdfUrl, ['rel' => 'nofollow noopener']) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '<p>' . Html::a(Html::encode(T::tr('View set on BrickAtlas')) . ' →', $url) . '</p>';

        return $html;
    }

    private function buildRetiringContentHtml(string $text, string $url, string $title, ?string $image, string $exitDay): string
    {
        $html = '<p>' . Html::encode($text) . '</p>';
        if ($image !== null) {
            $html .= '<p>' . Html::img($image, ['alt' => $title, 'style' => 'max-width:480px;height:auto;']) . '</p>';
        }
        $html .= '<p>' . Html::encode(T::tr('Retirement date: {date}', ['date' => $exitDay])) . '</p>';
        $html .= '<p>' . Html::a(Html::encode(T::tr('View set on BrickAtlas')) . ' →', $url) . '</p>';

        return $html;
    }

    // ─── helpers ──────────────────────────────────────────────────────────────

    /**
     * @return array{name:string,url:string}
     */
    private function feedAuthor(): array
    {
        return [
            'name' => self::AUTHOR_NAME,
            'url'  => Url::home(true),
        ];
    }

    /**
     * @return string[]
     */
    private function buildTags(string $kind, Set $set): array
    {
        $tags = [$kind];

        $themeSlug = trim((string)($set->theme->slug ?? ''));
        if ($themeSlug !== '') {
            $tags[] = 'theme-' . $themeSlug;
        }

        return $tags;
    }

    private function setUrl(Set $set): string
    {
        return Url::to(['/lego/' . $set->slug], true);
    }

    private function setImage(Set $set): ?string
    {
        $url = trim((string)$set->getDisplayMainImageUrl());
        if ($url === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return Url::to($url, true);
    }

    private function toIsoDate(mixed $value): ?string
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
}
