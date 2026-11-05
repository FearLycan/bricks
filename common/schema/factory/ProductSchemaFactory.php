<?php

namespace common\schema\factory;

use common\models\Set;
use common\models\SetImage;
use common\models\SetReview;
use yii\helpers\Url;

/**
 * Product node for a set page.
 *
 * Ratings and reviews come ONLY from BrickAtlas users (`set_review`,
 * published). Google's review-snippet guidelines forbid marking up ratings
 * aggregated from other websites, so retailer ratings/reviews imported into
 * `set_offer` / `set_offer_review` are deliberately left out of the markup
 * (they can still be shown on the page itself).
 */
final class ProductSchemaFactory
{
    private const MAX_REVIEWS = 5;

    public static function fromSet(Set $set, string $productUrl, ?array $offers): array
    {
        $productName = trim((string)$set->name);
        if ($productName === '') {
            $productName = 'LEGO Set';
        }

        $schema = [
            '@type'       => 'Product',
            '@id'         => $productUrl . '#product',
            'name'        => $productName,
            'sku'         => (string)$set->getSetNumberText(),
            'mpn'         => (string)$set->getSetNumberText(),
            'url'         => $productUrl,
            'description' => self::resolveDescription($set),
            'brand'       => [
                '@type' => 'Brand',
                'name'  => 'LEGO',
            ],
            'category'    => self::buildCategory($set),
        ];

        $images = self::collectImageUrls($set);
        if ($images !== []) {
            $schema['image'] = $images;
        }

        $publishedReviews = self::findPublishedReviews($set);

        $aggregateRating = self::buildAggregateRating($publishedReviews);
        if ($aggregateRating !== null) {
            $schema['aggregateRating'] = $aggregateRating;
        }

        $reviews = self::buildReviews($publishedReviews);
        if ($reviews !== []) {
            $schema['review'] = count($reviews) === 1 ? $reviews[0] : $reviews;
        }

        if ($offers !== null && $offers !== []) {
            $schema['offers'] = $offers;
        }

        return $schema;
    }

    /**
     * A Product node is only worth emitting when it carries one of the fields
     * Google requires for product snippets; otherwise Search Console reports
     * it as an invalid item.
     */
    public static function isEligible(array $schema): bool
    {
        return isset($schema['offers']) || isset($schema['aggregateRating']) || isset($schema['review']);
    }

    /**
     * @return SetReview[]
     */
    private static function findPublishedReviews(Set $set): array
    {
        if (!$set->id) {
            return [];
        }

        return SetReview::find()
            ->with('user')
            ->where([
                'set_id' => (int)$set->id,
                'status' => SetReview::STATUS_PUBLISHED,
            ])
            ->orderBy(['published_at' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
    }

    /**
     * @param SetReview[] $reviews
     */
    private static function buildAggregateRating(array $reviews): ?array
    {
        $sum = 0.0;
        $count = 0;
        foreach ($reviews as $review) {
            $score = (float)$review->overall_score;
            if ($score < SetReview::SCORE_MIN) {
                continue;
            }
            $sum += $score;
            $count++;
        }

        if ($count < 1) {
            return null;
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format($sum / $count, 1, '.', ''),
            'ratingCount' => $count,
            'reviewCount' => $count,
            'bestRating'  => (string)(int)SetReview::SCORE_MAX,
            'worstRating' => (string)(int)SetReview::SCORE_MIN,
        ];
    }

    /**
     * @param SetReview[] $reviews
     */
    private static function buildReviews(array $reviews): array
    {
        $result = [];
        foreach ($reviews as $review) {
            $score = (float)$review->overall_score;
            if ($score < SetReview::SCORE_MIN) {
                continue;
            }

            $authorName = trim((string)($review->user->username ?? ''));
            if ($authorName === '') {
                // Google requires a named author; skip anonymous entries.
                continue;
            }

            $node = [
                '@type'        => 'Review',
                'author'       => [
                    '@type' => 'Person',
                    'name'  => $authorName,
                ],
                'reviewRating' => [
                    '@type'       => 'Rating',
                    'ratingValue' => number_format($score, 1, '.', ''),
                    'bestRating'  => (string)(int)SetReview::SCORE_MAX,
                    'worstRating' => (string)(int)SetReview::SCORE_MIN,
                ],
            ];

            $title = trim(strip_tags((string)$review->title));
            if ($title !== '') {
                $node['name'] = $title;
            }

            $body = self::truncate(trim(strip_tags((string)$review->content)), 300);
            if ($body !== '') {
                $node['reviewBody'] = $body;
            }

            $published = trim((string)($review->published_at ?: $review->created_at));
            $timestamp = $published !== '' ? strtotime($published) : false;
            if ($timestamp !== false) {
                $node['datePublished'] = date('Y-m-d', $timestamp);
            }

            $result[] = $node;
            if (count($result) >= self::MAX_REVIEWS) {
                break;
            }
        }

        return $result;
    }

    private static function resolveDescription(Set $set): string
    {
        $description = trim(strip_tags((string)$set->description));
        if ($description === '') {
            return 'Detailed information about this LEGO set.';
        }

        return self::truncate($description, 220);
    }

    private static function truncate(string $value, int $limit): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit)) . '...';
    }

    private static function buildCategory(Set $set): string
    {
        $theme = trim((string)($set->theme->name ?? ''));
        $subtheme = trim((string)($set->subtheme->name ?? ''));

        if ($theme !== '' && $subtheme !== '') {
            return $theme . ' > ' . $subtheme;
        }

        return $theme !== '' ? $theme : 'LEGO Set';
    }

    private static function collectImageUrls(Set $set): array
    {
        $urls = [];
        $mainImageUrl = self::absolutize(trim($set->getDisplayMainImageUrl()));
        if ($mainImageUrl !== '' && !str_contains($mainImageUrl, 'placehold.co')) {
            $urls[] = $mainImageUrl;
        }

        foreach ($set->images as $image) {
            if (!$image instanceof SetImage) {
                continue;
            }

            $imageUrl = self::absolutize(trim((string)$image->url));
            if ($imageUrl === '' || in_array($imageUrl, $urls, true)) {
                continue;
            }

            $urls[] = $imageUrl;
        }

        return $urls;
    }

    private static function absolutize(string $url): string
    {
        if ($url === '' || preg_match('~^https?://~i', $url) === 1) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return Url::to('/' . ltrim($url, '/'), true);
    }
}
