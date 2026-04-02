<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_offer}}".
 *
 * @property int $id
 * @property int $set_id
 * @property int $store_id
 * @property string|null $external_id
 * @property string|null $name
 * @property string|null $url
 * @property string|null $image
 * @property string $currency_code
 * @property int|null $price
 * @property string|null $availability
 * @property float|null $rating_value
 * @property float|null $rating_scale_max
 * @property int $review_count
 * @property string|null $review_impressions
 * @property string|null $source
 * @property int $is_manual_override
 * @property string|null $synced_at
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Set $set
 * @property Store $store
 * @property SetOfferReview[] $setOfferReviews
 */
class SetOffer extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => date("Y-m-d H:i:s"),
            ],
        ];
    }

    public static function tableName(): string
    {
        return '{{%set_offer}}';
    }

    public function rules(): array
    {
        return [
            [['set_id', 'store_id'], 'required'],
            [['set_id', 'store_id', 'price', 'review_count', 'is_manual_override'], 'integer'],
            [['rating_value', 'rating_scale_max'], 'number'],
            [['review_impressions'], 'string'],
            [['synced_at', 'created_at', 'updated_at'], 'safe'],
            [['external_id'], 'string', 'max' => 100],
            [['name', 'url', 'image', 'availability'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
            [['source'], 'string', 'max' => 50],
            [['set_id', 'store_id', 'external_id'], 'unique', 'targetAttribute' => ['set_id', 'store_id', 'external_id']],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => Store::class, 'targetAttribute' => ['store_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'set_id' => 'Set ID',
            'store_id' => 'Store ID',
            'external_id' => 'External ID',
            'name' => 'Name',
            'url' => 'Url',
            'image' => 'Image',
            'currency_code' => 'Currency Code',
            'price' => 'Price',
            'availability' => 'Availability',
            'rating_value' => 'Rating Value',
            'rating_scale_max' => 'Rating Scale Max',
            'review_count' => 'Review Count',
            'review_impressions' => 'Review Impressions',
            'source' => 'Source',
            'is_manual_override' => 'Is Manual Override',
            'synced_at' => 'Synced At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSet(): ActiveQuery
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    public function getStore(): ActiveQuery
    {
        return $this->hasOne(Store::class, ['id' => 'store_id']);
    }

    public function getSetOfferReviews(): ActiveQuery
    {
        return $this->hasMany(SetOfferReview::class, ['set_offer_id' => 'id'])->orderBy(['reviewed_at' => SORT_DESC, 'id' => SORT_ASC]);
    }

    public function getDisplayNameOrDefault(string $default = '-'): string
    {
        if ($this->name !== null && $this->name !== '') {
            return $this->name;
        }

        if ($this->set && $this->set->name) {
            return $this->set->name;
        }

        return $default;
    }

    public function getFormattedPriceOrDefault(string $default = '-'): string
    {
        if ($this->price === null) {
            return $default;
        }

        return Set::formatAmountFromCents($this->price, $this->currency_code !== '' ? $this->currency_code : 'USD');
    }

    public function getDisplayRatingValue(): float
    {
        if ($this->rating_value !== null && (float)$this->rating_value > 0.0) {
            return round((float)$this->rating_value, 1);
        }

        $ratings = [];
        if ($this->isRelationPopulated('setOfferReviews')) {
            foreach ($this->setOfferReviews as $review) {
                if ($review->rating_value === null) {
                    continue;
                }
                $ratings[] = (float)$review->rating_value;
            }
        }

        if ($ratings === []) {
            return 0.0;
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }

    public function getDisplayReviewCount(): int
    {
        if ($this->review_count > 0) {
            return (int)$this->review_count;
        }

        if ($this->isRelationPopulated('setOfferReviews')) {
            return count($this->setOfferReviews);
        }

        return 0;
    }

    public function getRatingStarClasses(?float $rating = null): array
    {
        $normalizedRating = $rating !== null ? (float)$rating : $this->getDisplayRatingValue();
        $classes = [];
        for ($index = 1; $index <= 5; $index++) {
            $fullThreshold = (float)$index;
            $halfThreshold = $fullThreshold - 0.5;
            if ($normalizedRating >= $fullThreshold) {
                $classes[] = 'bi-star-fill';
                continue;
            }
            if ($normalizedRating >= $halfThreshold) {
                $classes[] = 'bi-star-half';
                continue;
            }
            $classes[] = 'bi-star';
        }

        return $classes;
    }

    public function getReviewImpressions(): array
    {
        if ($this->review_impressions === null || trim($this->review_impressions) === '') {
            return [];
        }

        try {
            $decoded = \yii\helpers\Json::decode($this->review_impressions, true);
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string)($item['label'] ?? ''));
            $num = isset($item['num']) && is_numeric($item['num'])
                ? (int)$item['num']
                : (isset($item['count']) && is_numeric($item['count']) ? (int)$item['count'] : 0);
            if ($label === '') {
                continue;
            }

            $result[] = [
                'label' => $label,
                'num' => max(0, $num),
            ];
        }

        return $result;
    }

    public static function syncBySetAndStore(Set $set, Store $store, array $items, string $source): void
    {
        $syncedIds = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $externalId = isset($item['external_id']) ? (string)$item['external_id'] : null;
            $query = self::find()->where(['set_id' => $set->id, 'store_id' => $store->id]);
            if ($externalId !== null && $externalId !== '') {
                $query->andWhere(['external_id' => $externalId]);
            } else {
                $query->andWhere(['name' => (string)($item['name'] ?? '')]);
            }

            $offer = $query->one();
            if (!$offer) {
                $offer = new self();
                $offer->set_id = $set->id;
                $offer->store_id = $store->id;
            }

            if ((int)$offer->is_manual_override === 1) {
                $syncedIds[] = $offer->id;
                continue;
            }

            $offer->external_id = $externalId ?: null;
            $offer->name = isset($item['name']) ? (string)$item['name'] : null;
            $offer->url = isset($item['url']) ? (string)$item['url'] : null;
            $offer->image = isset($item['image']) ? (string)$item['image'] : null;
            $offer->currency_code = isset($item['currency_code']) && is_string($item['currency_code']) && $item['currency_code'] !== '' ? strtoupper($item['currency_code']) : 'USD';
            $offer->price = isset($item['price']) && is_numeric($item['price'])
                ? (int)$item['price']
                : (isset($item['price_cents']) && is_numeric($item['price_cents']) ? (int)$item['price_cents'] : null);
            $offer->availability = isset($item['availability']) ? (string)$item['availability'] : null;
            $offer->rating_value = isset($item['rating_value']) && is_numeric($item['rating_value']) ? (float)$item['rating_value'] : null;
            $offer->rating_scale_max = isset($item['rating_scale_max']) && is_numeric($item['rating_scale_max']) ? (float)$item['rating_scale_max'] : 5.0;
            $offer->review_count = isset($item['review_count']) && is_numeric($item['review_count']) ? (int)$item['review_count'] : 0;
            $offer->source = $source;
            $offer->synced_at = date("Y-m-d H:i:s");
            $offer->save();

            $syncedIds[] = $offer->id;
        }

        if ($syncedIds === []) {
            return;
        }

        self::deleteAll([
            'and',
            ['set_id' => $set->id, 'store_id' => $store->id, 'is_manual_override' => 0],
            ['not in', 'id', $syncedIds],
        ]);
    }
}
