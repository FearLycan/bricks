<?php

namespace common\schema\builder;

use common\models\Set;
use common\schema\factory\OfferSchemaFactory;
use common\schema\factory\ProductSchemaFactory;

final class SetPageSchemaBuilder
{
    public static function build(Set $set, string $productUrl): array
    {
        $offerSchemas = OfferSchemaFactory::fromSet($set, $productUrl);
        $productSchema = ProductSchemaFactory::fromSet($set, $productUrl, $offerSchemas);

        return [
            $productSchema,
        ];
    }
}
