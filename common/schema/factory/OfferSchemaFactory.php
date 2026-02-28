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

            $currency = strtoupper((string)$setOffer->currency_code);
            if ($currency === '') {
                $currency = 'USD';
            }

            $offers[] = [
                '@type' => 'Offer',
                'price' => number_format(($setOffer->price ?? 0) / 100, 2, '.', ''),
                'priceCurrency' => $currency,
                'availability' => self::resolveAvailability($setOffer->availability),
                'priceValidUntil' => self::resolvePriceValidUntil(),
                'url' => $setOffer->url ?: $productUrl,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $setOffer->store->name ?? 'Store',
                ],
            ];
        }

        if ($offers !== []) {
            return $offers;
        }

        foreach ($set->setPrices as $setPrice) {
            if (!$setPrice instanceof SetPrice) {
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
                'priceValidUntil' => self::resolvePriceValidUntil(),
                'url'             => $productUrl,
            ];
        }

        if ($offers !== []) {
            return $offers;
        }

        $fallbackPriceCents = $set->price ?? 0;

        return [[
                    '@type'           => 'Offer',
                    'price'           => number_format($fallbackPriceCents / 100, 2, '.', ''),
                    'priceCurrency'   => 'USD',
                    'availability'    => self::resolveAvailability($set->availability),
                    'priceValidUntil' => self::resolvePriceValidUntil(),
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

    private static function resolvePriceValidUntil(): string
    {
        return '2099-12-31';
    }
}
