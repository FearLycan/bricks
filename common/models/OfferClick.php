<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * One click on a store offer (kind "offer") or on the official LEGO price button (kind "lego").
 * Written by frontend\controllers\ClickController, read by the admin Clicks page.
 *
 * @property int         $id
 * @property string      $kind
 * @property int         $set_id
 * @property int|null    $set_offer_id
 * @property int|null    $store_id
 * @property int|null    $price
 * @property string|null $currency_code
 * @property string|null $lang
 * @property string|null $device
 * @property string|null $ua_hash
 * @property bool        $is_bot
 * @property int         $created_at
 *
 * @property Set|null    $set
 * @property Store|null  $store
 */
class OfferClick extends ActiveRecord
{
    public const KIND_OFFER = 'offer';
    public const KIND_LEGO = 'lego';

    public static function tableName(): string
    {
        return '{{%offer_click}}';
    }

    public function getSet()
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::class, ['id' => 'store_id']);
    }
}
