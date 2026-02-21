<?php

namespace common\models;

use common\enums\image\KindEnum;
use common\enums\image\TypeEnum;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%set}}".
 *
 * @property int         $id
 * @property string|null $number
 * @property string|null $name
 * @property string|null $slug
 * @property int         $theme_id
 * @property int|null    $subtheme_id
 * @property int|null    $status
 * @property int|null    $number_variant
 * @property int|null    $minifigures
 * @property int|null    $year
 * @property int|null    $pieces
 * @property int|null    $released
 * @property float|null  $rating
 * @property int|null    $price
 * @property string|null $brickset_url
 * @property string|null $dimensions
 * @property string|null $availability
 * @property int|null    $age
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property SetImage[]  $images
 * @property SetPrice[]  $setPrices
 * @property Theme       $theme
 * @property Theme|null  $subtheme
 */
class Set extends ActiveRecord
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
            'sluggable' => [
                'class'         => SluggableBehavior::class,
                'attribute'     => ['name', 'number'],
                'slugAttribute' => 'slug',
                'ensureUnique'  => false,
                'immutable'     => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%set}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['theme_id'], 'required'],
            [['theme_id', 'subtheme_id', 'status', 'number_variant', 'minifigures', 'year', 'pieces', 'released', 'age', 'price'], 'integer'],
            [['rating'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['number'], 'string', 'max' => 30],
            [['name', 'slug', 'brickset_url', 'dimensions', 'availability'], 'string', 'max' => 255],
            [['theme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Theme::class, 'targetAttribute' => ['theme_id' => 'id']],
            [['subtheme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Theme::class, 'targetAttribute' => ['subtheme_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'             => 'ID',
            'number'         => 'Number',
            'name'           => 'Name',
            'slug'           => 'Slug',
            'theme_id'       => 'Theme ID',
            'subtheme_id'    => 'Subtheme ID',
            'status'         => 'Status',
            'number_variant' => 'Number Variant',
            'minifigures'    => 'Minifigures',
            'year'           => 'Year',
            'pieces'         => 'Pieces',
            'released'       => 'Released',
            'brickset_url'   => 'Brickset Url',
            'dimensions'     => 'Dimensions',
            'availability'   => 'Availability',
            'age'            => 'Age',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
        ];
    }

    /**
     * Gets query for [[SetImages]].
     *
     * @return ActiveQuery
     */
    public function getImages(): ActiveQuery
    {
        return $this->hasMany(SetImage::class, ['set_id' => 'id']);
    }

    /**
     * Gets query for [[SetPrices]].
     *
     * @return ActiveQuery
     */
    public function getSetPrices(): ActiveQuery
    {
        return $this->hasMany(SetPrice::class, ['set_id' => 'id']);
    }

    /**
     * Gets query for [[Theme]].
     *
     * @return ActiveQuery
     */
    public function getTheme(): ActiveQuery
    {
        return $this->hasOne(Theme::class, ['id' => 'theme_id']);
    }

    /**
     * Gets query for [[Subtheme]].
     *
     * @return ActiveQuery
     */
    public function getSubtheme(): ActiveQuery
    {
        return $this->hasOne(Theme::class, ['id' => 'subtheme_id']);
    }

    public function getMainImage(): ?SetImage
    {
        return SetImage::findOne([
            'set_id' => $this->id,
            'kind'   => KindEnum::MAIN->value,
            'type'   => TypeEnum::IMAGE->value,
        ]);
    }

    public function getDisplayMainImage(): ?SetImage
    {
        $mainImage = $this->getMainImage();
        if ($mainImage !== null) {
            return $mainImage;
        }

        if (isset($this->images[0])) {
            return $this->images[0];
        }

        return null;
    }

    public function getDisplayMainImageUrl(): string
    {
        $mainImage = $this->getDisplayMainImage();
        if ($mainImage !== null) {
            return Url::to($mainImage->url);
        }

        return 'https://placehold.co/1000x800?text=' . rawurlencode((string) $this->number);
    }

    public static function formatAmountFromCents(int $amountInCents, string $currency = 'PLN'): string
    {
        return number_format($amountInCents / 100, 2, ',', ' ') . ' ' . $currency;
    }

    public function getFormattedPrice(string $currency = 'PLN'): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return self::formatAmountFromCents($this->price, $currency);
    }

    public function getFormattedPriceOrDefault(string $defaultText, string $currency = 'PLN'): string
    {
        $price = $this->getFormattedPrice($currency);
        if ($price !== null) {
            return $price;
        }

        return $defaultText;
    }

    public function getThemeGroupNameOrDefault(string $defaultText = '-'): string
    {
        if ($this->theme->group && $this->theme->group->name) {
            return $this->theme->group->name;
        }

        return $defaultText;
    }

    public function getSubthemeNameOrDefault(string $defaultText = '-'): string
    {
        if ($this->subtheme && $this->subtheme->name) {
            return $this->subtheme->name;
        }

        return $defaultText;
    }

    public function getAgeText(string $defaultText = '-'): string
    {
        if ($this->age) {
            return $this->age . '+';
        }

        return $defaultText;
    }

    public function getPiecesText(string $defaultText = '-'): string
    {
        if ($this->pieces !== null) {
            return (string) $this->pieces;
        }

        return $defaultText;
    }

    public function getMinifiguresText(string $defaultText = '-'): string
    {
        if ($this->minifigures !== null) {
            return (string) $this->minifigures;
        }

        return $defaultText;
    }

    public function getYearText(string $defaultText = '-'): string
    {
        if ($this->year !== null) {
            return (string) $this->year;
        }

        return $defaultText;
    }

    public function getSetNumberText(string $defaultText = '-'): string
    {
        if ($this->number !== null && $this->number !== '') {
            return $this->number;
        }

        return $defaultText;
    }

    public function getAvailabilityText(string $defaultText = '-'): string
    {
        if ($this->availability !== null && $this->availability !== '') {
            return $this->availability;
        }

        return $defaultText;
    }

    public function getDimensionsDisplayText(string $defaultText = '-'): string
    {
        $dimensions = $this->getDimensionsData();
        if ($dimensions === null) {
            return $defaultText;
        }

        $heightCm = self::formatDimensionCm($dimensions['height_cm']);
        $widthCm = self::formatDimensionCm($dimensions['width_cm']);
        $depthCm = self::formatDimensionCm($dimensions['depth_cm']);

        $heightIn = (int) round($dimensions['height_cm'] / 2.54);
        $widthIn = (int) round($dimensions['width_cm'] / 2.54);
        $depthIn = (int) round($dimensions['depth_cm'] / 2.54);

        return "H: {$heightIn}\" ({$heightCm}cm) W: {$widthIn}\" ({$widthCm}cm) D: {$depthIn}\" ({$depthCm}cm)";
    }

    private function getDimensionsData(): ?array
    {
        if (!$this->dimensions) {
            return null;
        }

        try {
            $decoded = Json::decode($this->dimensions, true);
        } catch (\Throwable $exception) {
            return null;
        }

        if (!is_array($decoded)) {
            return null;
        }

        if (
            !isset($decoded['dimension1'], $decoded['dimension2'], $decoded['dimension3']) ||
            !is_numeric($decoded['dimension1']) ||
            !is_numeric($decoded['dimension2']) ||
            !is_numeric($decoded['dimension3'])
        ) {
            return null;
        }

        return [
            'height_cm' => (float) $decoded['dimension1'],
            'width_cm' => (float) $decoded['dimension2'],
            'depth_cm' => (float) $decoded['dimension3'],
        ];
    }

    private static function formatDimensionCm(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }
}
