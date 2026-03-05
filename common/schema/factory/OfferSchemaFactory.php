<?php

namespace common\schema\factory;

use common\models\Set;
use common\models\SetOffer;
use common\models\SetPrice;

final class OfferSchemaFactory
{
    public static function fromSet(Set $set, string $productUrl): array
    {
        $offers = [];
        foreach ($set->setOffers as $setOffer) {
            if (!$setOffer instanceof SetOffer) {
                continue;
            }

            if ($setOffer->price === null || $setOffer->price <= 0) {
                continue;
            }

            $currency = strtoupper((string)$setOffer->currency_code);
            if ($currency === '') {
                $currency = 'USD';
            }

            $offerSchema = [
                '@type' => 'Offer',
                'price' => number_format(($setOffer->price ?? 0) / 100, 2, '.', ''),
                'priceCurrency' => $currency,
                'availability' => self::resolveAvailability($setOffer->availability),
                'url' => $setOffer->url ?: $productUrl,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $setOffer->store->name ?? 'Store',
                ],
            ];

            if ($set->price !== null && $set->price > 0 && $currency === 'USD' && $setOffer->price !== null && $setOffer->price > 0 && $setOffer->price < $set->price) {
                $offerSchema['priceSpecification'] = self::buildPromotionPriceSpecification($set->price, $setOffer->price, $currency);
            }

            $offers[] = $offerSchema;
        }

        if ($offers !== []) {
            return $offers;
        }

        foreach ($set->setPrices as $setPrice) {
            if (!$setPrice instanceof SetPrice) {
                continue;
            }

            if ($setPrice->retail_price_cents <= 0) {
                continue;
            }

            $currency = self::resolveCurrencyCode($setPrice->country_code);
            if ($currency === null) {
                continue;
            }

            $offers[] = [
                '@type'           => 'Offer',
                'price'           => number_format($setPrice->retail_price_cents / 100, 2, '.', ''),
                'priceCurrency'   => $currency,
                'availability'    => self::resolveAvailability($set->availability),
                'url'             => $productUrl,
            ];
        }

        if ($offers !== []) {
            return $offers;
        }

        $fallbackPriceCents = $set->price ?? 0;
        if ($fallbackPriceCents <= 0) {
            return [];
        }

        return [[
                    '@type'           => 'Offer',
                    'price'           => number_format($fallbackPriceCents / 100, 2, '.', ''),
                    'priceCurrency'   => 'USD',
                    'availability'    => self::resolveAvailability($set->availability),
                    'url'             => $productUrl,
                ]];
    }

    private static function resolveCurrencyCode(string $countryCode): ?string
    {
        return match (strtoupper($countryCode)) {
            'US' => 'USD',
            'PL' => 'PLN',
            'CA' => 'CAD',
            'UK', 'GB' => 'GBP',
            'DE' => 'EUR',
            default => null,
        };
    }

    private static function resolveAvailability(?string $value): string
    {
        $normalized = strtolower(trim((string)$value));
        if ($normalized === '') {
            return 'https://schema.org/InStock';
        }

        if (str_contains($normalized, 'retired') || str_contains($normalized, 'sold out') || str_contains($normalized, 'out of stock')) {
            return 'https://schema.org/OutOfStock';
        }

        if (str_contains($normalized, 'preorder') || str_contains($normalized, 'pre-order')) {
            return 'https://schema.org/PreOrder';
        }

        return 'https://schema.org/InStock';
    }

    private static function buildPromotionPriceSpecification(int $listPriceCents, int $salePriceCents, string $currency): array
    {
        return [
            [
                '@type' => 'UnitPriceSpecification',
                'name' => 'List price',
                'price' => number_format($listPriceCents / 100, 2, '.', ''),
                'priceCurrency' => $currency,
            ],
            [
                '@type' => 'UnitPriceSpecification',
                'name' => 'Sale price',
                'price' => number_format($salePriceCents / 100, 2, '.', ''),
                'priceCurrency' => $currency,
            ],
        ];
    }
}
