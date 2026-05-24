<?php

namespace frontend\components;

use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetInstruction;
use common\models\SetOffer;
use DateTimeImmutable;
use DateTimeInterface;
use Yii;
use yii\helpers\Url;

/**
 * Builds the JSON Feed 1.1 payload published at /feed.
 *
 * Each item is one catalog event. Event types and their sources:
 *   - new_set         → `set.created_at` desc
 *   - new_offer       → `set_offer.created_at` desc (proxy for "price change" — we
 *                       don't track price history)
 *   - new_instruction → `set_instruction.created_at` desc
 *   - retiring_soon   → `set.exit_date` in [today, +90d]
 *
 * The four streams are merged and the top {@see self::MAX_ITEMS} most recent items
 * are returned. Title/content_text are translated via {@see T::tr()} so the same
 * service powers both /feed (EN) and /pl/feed (PL).
 */
final class FeedService
{
    public const MAX_ITEMS = 50;
    public const CACHE_TTL = 300;

    private const RETIRING_WINDOW_DAYS = 90;

    public function buildFeed(): array
    {
        $language = (string)Yii::$app->language;
        $cacheKey = 'feed.json.' . $language;

        return Yii::$app->cache->getOrSet($cacheKey, function () use ($language) {
            return [
                'version'       => 'https://jsonfeed.org/version/1.1',
                'title'         => T::tr('BrickAtlas — LEGO catalog updates'),
                'description'   => T::tr('Latest catalog activity on BrickAtlas: new sets, fresh store offers, published PDF instructions and sets approaching retirement.'),
                'home_page_url' => Url::home(true),
                'feed_url'      => Url::to(['/feed'], true),
                'language'      => $language,
                'items'         => $this->buildItems(),
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

        // Sort by date_published desc, then take the most recent slice. We over-fetch
        // per-stream so the global top-N is balanced across event types.
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

            $items[] = [
                'id'             => 'set-new-' . (int)$set->id,
                'url'            => $this->setUrl($set),
                'title'          => T::tr('New set: {number} {name}', [
                    'number' => $set->getSetNumberText(),
                    'name'   => (string)$set->name,
                ]),
                'content_text'   => $this->buildNewSetContent($set),
                'image'          => $this->setImage($set),
                'date_published' => $publishedAt,
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

            $price = $offer->getFormattedPriceOrDefault('-');
            $storeName = (string)($offer->store->name ?? T::tr('a store'));

            $items[] = [
                'id'             => 'offer-new-' . (int)$offer->id,
                'url'            => $this->setUrl($set),
                'title'          => T::tr('New offer: {number} {name}', [
                    'number' => $set->getSetNumberText(),
                    'name'   => (string)$set->name,
                ]),
                'content_text'   => T::tr('New offer for {name} ({number}): {price} at {store}.', [
                    'name'   => (string)$set->name,
                    'number' => $set->getSetNumberText(),
                    'price'  => $price,
                    'store'  => $storeName,
                ]),
                'image'          => $this->setImage($set),
                'date_published' => $publishedAt,
                '_kind'          => 'new_offer',
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildNewInstructionItems(): array
    {
        $instructions = SetInstruction::find()
            ->alias('i')
            ->innerJoin(['s' => Set::tableName()], 's.id = i.set_id AND s.status = ' . StatusEnum::ACTIVE->value)
            ->andWhere(['not', ['s.slug' => null]])
            ->andWhere(['<>', 's.slug', ''])
            ->with(['set.theme'])
            ->orderBy(['i.created_at' => SORT_DESC, 'i.id' => SORT_DESC])
            ->limit(self::MAX_ITEMS)
            ->all();

        $items = [];
        foreach ($instructions as $instruction) {
            $set = $instruction->set;
            if ($set === null) {
                continue;
            }

            $publishedAt = $this->toIsoDate($instruction->created_at);
            if ($publishedAt === null) {
                continue;
            }

            $items[] = [
                'id'             => 'instruction-new-' . (int)$instruction->id,
                'url'            => $this->setUrl($set),
                'title'          => T::tr('New PDF instruction: {number} {name}', [
                    'number' => $set->getSetNumberText(),
                    'name'   => (string)$set->name,
                ]),
                'content_text'   => T::tr('Building instruction PDF published for {name} ({number}).', [
                    'name'   => (string)$set->name,
                    'number' => $set->getSetNumberText(),
                ]),
                'image'          => $this->setImage($set),
                'date_published' => $publishedAt,
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

            // Use the most recent update timestamp as the announcement date so the
            // event surfaces when the exit_date is first set, not when it triggers.
            $publishedAt = $this->toIsoDate($set->updated_at ?? $set->created_at) ?? $exitDate;

            $items[] = [
                'id'             => 'set-retiring-' . (int)$set->id . '-' . substr($exitDate, 0, 10),
                'url'            => $this->setUrl($set),
                'title'          => T::tr('Retiring soon: {number} {name}', [
                    'number' => $set->getSetNumberText(),
                    'name'   => (string)$set->name,
                ]),
                'content_text'   => T::tr('{name} ({number}) is scheduled to retire on {date}.', [
                    'name'   => (string)$set->name,
                    'number' => $set->getSetNumberText(),
                    'date'   => substr((string)$set->exit_date, 0, 10),
                ]),
                'image'          => $this->setImage($set),
                'date_published' => $publishedAt,
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
