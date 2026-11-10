<?php

namespace common\schema\builder;

use common\models\Set;
use common\schema\factory\OfferSchemaFactory;
use common\schema\factory\ProductSchemaFactory;

final class SetPageSchemaBuilder
{
    /**
     * @param string $productUrl absolute URL of the set page; query string and
     *                           fragment are stripped so it matches the canonical
     */
    public static function build(Set $set, string $productUrl): array
    {
        $productUrl = preg_replace('~[?#].*$~', '', $productUrl) ?? $productUrl;

        $aggregateOffer = OfferSchemaFactory::fromSet($set, $productUrl);
        $productSchema = ProductSchemaFactory::fromSet($set, $productUrl, $aggregateOffer);

        if (!ProductSchemaFactory::isEligible($productSchema)) {
            return [];
        }

        return [
            $productSchema,
        ];
    }
}
