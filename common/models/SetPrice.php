<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_price}}".
 *
 * @property int         $id
 * @property int         $set_id
 * @property string      $country_code
 * @property int         $retail_price_cents
 * @property string|null $date_first_available
 * @property string|null $date_last_available
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property Set         $set
 */
class SetPrice extends ActiveRecord
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value'      => date("Y-m-d H:i:s"),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%set_price}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['set_id', 'country_code', 'retail_price_cents'], 'required'],
            [['set_id', 'retail_price_cents'], 'integer'],
            [['date_first_available', 'date_last_available', 'created_at', 'updated_at'], 'safe'],
            [['country_code'], 'string', 'max' => 2],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
            [['country_code', 'set_id'], 'unique', 'targetAttribute' => ['country_code', 'set_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'                   => 'ID',
            'set_id'               => 'Set ID',
            'country_code'         => 'Country Code',
            'retail_price_cents'   => 'Retail Price Cents',
            'date_first_available' => 'Date First Available',
            'date_last_available'  => 'Date Last Available',
            'created_at'           => 'Created At',
            'updated_at'           => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Set]].
     *
     * @return ActiveQuery
     */
    public function getSet(): ActiveQuery
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    public static function syncLegoComPrices(Set $set, array $pricesByCountry): void
    {
        foreach ($pricesByCountry as $countryCode => $countryPriceData) {
            if (!is_array($countryPriceData)) {
                continue;
            }

            if (!isset($countryPriceData['retailPrice']) || !is_numeric($countryPriceData['retailPrice'])) {
                continue;
            }

            $normalizedCountryCode = strtoupper((string) $countryCode);
            $price = self::findOne([
                'set_id' => $set->id,
                'country_code' => $normalizedCountryCode,
            ]);

            if (!$price) {
                $price = new self();
                $price->set_id = $set->id;
                $price->country_code = $normalizedCountryCode;
            }

            $price->retail_price_cents = (int) round(((float) $countryPriceData['retailPrice']) * 100);
            $price->date_first_available = self::normalizeApiDate($countryPriceData['dateFirstAvailable'] ?? null);
            $price->date_last_available = self::normalizeApiDate($countryPriceData['dateLastAvailable'] ?? null);
            $price->save();
        }
    }

    private static function normalizeApiDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
