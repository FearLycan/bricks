<?php

namespace common\schema\factory;

use common\models\Set;
use common\models\SetImage;
use common\models\SetOffer;
use common\models\SetOfferReview;

final class ProductSchemaFactory
{
    public static function fromSet(Set $set, string $productUrl, array $offers): array
    {
        $productName = trim((string)$set->name);
        if ($productName === '') {
            $productName = 'LEGO Set';
        }

        $schema = [
            '@type'       => 'Product',
            '@id'         => '#product',
            'name'        => $productName,
            'sku'         => (string)$set->getSetNumberText(),
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

        $reviews = self::buildReviews($set);
        if ($reviews !== []) {
            $schema['review'] = count($reviews) === 1 ? $reviews[0] : $reviews;
        }

        $aggregateRating = self::buildAggregateRating($set, $reviews);
        if ($aggregateRating !== null) {
            $schema['aggregateRating'] = $aggregateRating;
        }

        if ($offers !== []) {
            $schema['offers'] = count($offers) === 1 ? $offers[0] : $offers;
        }

        return $schema;
    }

    private static function resolveDescription(Set $set): string
    {
        $description = trim(strip_tags((string)$set->description));
        if ($description === '') {
            return 'Detailed information about this LEGO set.';
        }

        if (mb_strlen($description) <= 220) {
            return $description;
        }

        return rtrim(mb_substr($description, 0, 220)) . '...';
    }

    private static function buildAggregateRating(Set $set, array $reviews): ?array
    {
        $aggregateFromOffers = self::buildAggregateRatingFromOffers($set);
        if ($aggregateFromOffers !== null) {
            return $aggregateFromOffers;
        }

        return self::buildAggregateRatingFromReviews($reviews);
    }

    private static function buildAggregateRatingFromOffers(Set $set): ?array
    {
        $weightedRatingSum = 0.0;
        $reviewCount = 0;

        foreach ($set->setOffers as $offer) {
            if (!$offer instanceof SetOffer) {
                continue;
            }

            $offerReviewCount = max(0, (int)$offer->review_count);
            $offerRatingValue = (float)$offer->rating_value;
            if ($offerReviewCount < 1 || $offerRatingValue <= 0) {
                continue;
            }

            $ratingScaleMax = (float)$offer->rating_scale_max;
            if ($ratingScaleMax <= 0) {
                $ratingScaleMax = 5.0;
            }

            $normalizedRating = min(5.0, max(0.0, ($offerRatingValue / $ratingScaleMax) * 5.0));
            $weightedRatingSum += $normalizedRating * $offerReviewCount;
            $reviewCount += $offerReviewCount;
        }

        if ($reviewCount < 1) {
            return null;
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format($weightedRatingSum / $reviewCount, 1, '.', ''),
            'reviewCount' => $reviewCount,
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }

    private static function buildAggregateRatingFromReviews(array $reviews): ?array
    {
        $ratingSum = 0.0;
        $ratingCount = 0;

        foreach ($reviews as $review) {
            $ratingValue = isset($review['reviewRating']['ratingValue']) ? (float)$review['reviewRating']['ratingValue'] : 0.0;
            if ($ratingValue <= 0) {
                continue;
            }

            $ratingSum += $ratingValue;
            $ratingCount++;
        }

        if ($ratingCount < 1) {
            return null;
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format($ratingSum / $ratingCount, 1, '.', ''),
            'reviewCount' => $ratingCount,
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }

    private static function buildReviews(Set $set): array
    {
        $reviews = [];

        foreach ($set->setOffers as $offer) {
            if (!$offer instanceof SetOffer) {
                continue;
            }

            foreach ($offer->setOfferReviews as $review) {
                if (!$review instanceof SetOfferReview) {
                    continue;
                }

                $reviewBody = self::resolveReviewBody($review);
                $hasRating = $review->rating_value !== null && (float)$review->rating_value > 0;
                if ($reviewBody === '' && !$hasRating) {
                    continue;
                }

                $authorName = trim((string)$review->author_name);
                $reviewSchema = [
                    '@type'      => 'Review',
                    'author'     => [
                        '@type' => $authorName !== '' ? 'Person' : 'Organization',
                        'name'  => $authorName !== '' ? $authorName : ($offer->store->name ?? 'Store'),
                    ],
                ];

                if ($reviewBody !== '') {
                    $reviewSchema['reviewBody'] = $reviewBody;
                }

                $reviewTitle = trim(strip_tags((string)$review->title));
                if ($reviewTitle !== '') {
                    $reviewSchema['name'] = $reviewTitle;
                }

                $publishedAt = trim((string)$review->reviewed_at);
                if ($publishedAt !== '') {
                    $publishedAtTimestamp = strtotime($publishedAt);
                    if ($publishedAtTimestamp !== false) {
                        $reviewSchema['datePublished'] = date(DATE_ATOM, $publishedAtTimestamp);
                    }
                }

                if ($hasRating) {
                    $ratingScaleMax = (float)$review->rating_scale_max;
                    if ($ratingScaleMax <= 0) {
                        $ratingScaleMax = 5.0;
                    }

                    $reviewSchema['reviewRating'] = [
                        '@type'       => 'Rating',
                        'ratingValue' => number_format(min(5.0, max(0.0, ((float)$review->rating_value / $ratingScaleMax) * 5.0)), 1, '.', ''),
                        'bestRating'  => '5',
                        'worstRating' => '1',
                    ];
                }

                $reviews[] = $reviewSchema;
                if (count($reviews) >= 5) {
                    return $reviews;
                }
            }
        }

        return $reviews;
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
        $mainImageUrl = trim($set->getDisplayMainImageUrl());
        if ($mainImageUrl !== '') {
            $urls[] = $mainImageUrl;
        }

        foreach ($set->images as $image) {
            if (!$image instanceof SetImage) {
                continue;
            }

            $imageUrl = trim((string)$image->url);
            if ($imageUrl === '' || in_array($imageUrl, $urls, true)) {
                continue;
            }

            $urls[] = $imageUrl;
        }

        return $urls;
    }

    private static function resolveReviewBody(SetOfferReview $review): string
    {
        $parts = [];

        $title = trim(strip_tags((string)$review->title));
        if ($title !== '') {
            $parts[] = $title;
        }

        $content = trim(strip_tags((string)$review->content));
        if ($content !== '') {
            $parts[] = $content;
        }

        $body = trim(implode(' ', $parts));
        if ($body === '') {
            return '';
        }

        if (mb_strlen($body) <= 300) {
            return $body;
        }

        return rtrim(mb_substr($body, 0, 300)) . '...';
    }
}
