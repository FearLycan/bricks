<?php

namespace common\schema\factory;

use common\models\Set;
use common\models\SetOffer;

/**
 * Builds the `offers` node for a set page.
 *
 * BrickAtlas is a price-comparison site, not a seller, so Google expects a
 * single `AggregateOffer` (lowPrice / highPrice / offerCount) rather than a
 * list of `Offer` nodes that look like we sell the product ourselves.
 *
 * Only real retailer offers with a price are used. The LEGO retail price
 * (MSRP from `set_price` / `set.price`) is NOT an offer — nobody sells at
 * that price on this page — so it is never emitted as one.
 */
final class OfferSchemaFactory
{
    public static function fromSet(Set $set, string $productUrl): ?array
    {
        $pricesByCurrency = [];
        foreach ($set->setOffers as $setOffer) {
            if (!$setOffer instanceof SetOffer) {
                continue;
            }

            if ($setOffer->price === null || $setOffer->price <= 0) {
                continue;
            }

            $currency = strtoupper(trim((string)$setOffer->currency_code));
            if ($currency === '') {
                $currency = 'USD';
            }

            $pricesByCurrency[$currency][] = [
                'price'     => (int)$setOffer->price,
                'available' => self::isAvailable($setOffer->availability),
            ];
        }

        if ($pricesByCurrency === []) {
            return null;
        }

        // AggregateOffer must use a single currency. Pick the one with the
        // most offers; on a tie prefer USD, then alphabetical for stability.
        uksort($pricesByCurrency, static function (string $a, string $b) use ($pricesByCurrency): int {
            $countDiff = count($pricesByCurrency[$b]) <=> count($pricesByCurrency[$a]);
            if ($countDiff !== 0) {
                return $countDiff;
            }
            if ($a === 'USD' || $b === 'USD') {
                return $a === 'USD' ? -1 : 1;
            }

            return strcmp($a, $b);
        });

        $currency = array_key_first($pricesByCurrency);
        $entries = $pricesByCurrency[$currency];
        $prices = array_column($entries, 'price');
        $anyAvailable = in_array(true, array_column($entries, 'available'), true);

        return [
            '@type'         => 'AggregateOffer',
            'priceCurrency' => $currency,
            'lowPrice'      => self::formatPrice(min($prices)),
            'highPrice'     => self::formatPrice(max($prices)),
            'offerCount'    => count($prices),
            'availability'  => $anyAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url'           => $productUrl,
        ];
    }

    private static function formatPrice(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private static function isAvailable(?string $value): bool
    {
        $normalized = strtolower(trim((string)$value));
        if ($normalized === '') {
            // Retailer feeds usually omit availability for in-stock items.
            return true;
        }

        foreach (['retired', 'sold out', 'out of stock', 'unavailable', 'niedostępn'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return false;
            }
        }

        return true;
    }
}
