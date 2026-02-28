<?php

namespace common\schema\factory;

use common\models\Set;
use common\models\SetImage;

final class ProductSchemaFactory
{
    public static function fromSet(Set $set, string $productUrl, array $offers): array
    {
        $productName = trim((string)$set->name);
        if ($productName === '') {
            $productName = 'LEGO Set';
        }

        $schema = [
            '@type'           => 'Product',
            '@id'             => '#product',
            'name'            => $productName,
            'sku'             => (string)$set->getSetNumberText(),
            'url'             => $productUrl,
            'description'     => self::resolveDescription($set),
            'brand'           => [
                '@type' => 'Brand',
                'name'  => 'LEGO',
            ],
            'category'        => self::buildCategory($set),
            'aggregateRating' => self::buildAggregateRating($set),
            'review'          => self::buildReview($set),
        ];

        $images = self::collectImageUrls($set);
        if ($images !== []) {
            $schema['image'] = $images;
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

    private static function buildAggregateRating(Set $set): array
    {
        $ratingValue = is_numeric($set->rating) ? (float)$set->rating : 4.5;
        if ($ratingValue <= 0) {
            $ratingValue = 4.5;
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format($ratingValue, 1, '.', ''),
            'reviewCount' => 1,
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }

    private static function buildReview(Set $set): array
    {
        $reviewBody = trim(strip_tags((string)$set->description));
        if ($reviewBody === '') {
            $reviewBody = 'Popular LEGO set with high build and display value.';
        }

        return [
            '@type'        => 'Review',
            'author'       => [
                '@type' => 'Organization',
                'name'  => 'LEGO Catalog',
            ],
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => is_numeric($set->rating) && (float)$set->rating > 0
                    ? number_format((float)$set->rating, 1, '.', '')
                    : '4.5',
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'reviewBody'   => $reviewBody,
        ];
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
}
