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

        $aggregateRating = self::buildAggregateRating($set);
        if ($aggregateRating !== null) {
            $schema['aggregateRating'] = $aggregateRating;
        }

        $review = self::buildReview($set);
        if ($review !== null) {
            $schema['review'] = $review;
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

    private static function buildAggregateRating(Set $set): ?array
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

    private static function buildReview(Set $set): ?array
    {
        foreach ($set->setOffers as $offer) {
            if (!$offer instanceof SetOffer) {
                continue;
            }

            foreach ($offer->setOfferReviews as $review) {
                if (!$review instanceof SetOfferReview) {
                    continue;
                }

                $reviewBody = self::resolveReviewBody($review);
                if ($reviewBody === '') {
                    continue;
                }

                $authorName = trim((string)$review->author_name);
                $reviewSchema = [
                    '@type'      => 'Review',
                    'author'     => [
                        '@type' => $authorName !== '' ? 'Person' : 'Organization',
                        'name'  => $authorName !== '' ? $authorName : ($offer->store->name ?? 'Store'),
                    ],
                    'reviewBody' => $reviewBody,
                ];

                if ($review->rating_value !== null && (float)$review->rating_value > 0) {
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

                return $reviewSchema;
            }
        }

        return null;
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
