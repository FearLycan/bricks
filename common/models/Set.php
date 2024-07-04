<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%set}}".
 *
 * @property int         $id
 * @property string|null $number
 * @property string|null $name
 * @property string|null $slug
 * @property int         $theme_id
 * @property int|null    $status
 * @property int|null    $number_variant
 * @property int|null    $minifigures
 * @property int|null    $year
 * @property int|null    $pieces
 * @property int|null    $released
 * @property string|null $brickset_url
 * @property int|null    $age
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property SetImage[]  $images
 * @property Set[]       $sets
 * @property Set         $theme
 */
class Set extends ActiveRecord
{
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
            [['theme_id', 'status', 'number_variant', 'minifigures', 'year', 'pieces', 'released', 'age'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['number'], 'string', 'max' => 30],
            [['name', 'slug', 'brickset_url'], 'string', 'max' => 255],
            [['theme_id'], 'exist', 'skipOnError' => true, 'targetClass' => __CLASS__, 'targetAttribute' => ['theme_id' => 'id']],
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
            'status'         => 'Status',
            'number_variant' => 'Number Variant',
            'minifigures'    => 'Minifigures',
            'year'           => 'Year',
            'pieces'         => 'Pieces',
            'released'       => 'Released',
            'brickset_url'   => 'Brickset Url',
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
     * Gets query for [[Sets]].
     *
     * @return ActiveQuery
     */
    public function getSets(): ActiveQuery
    {
        return $this->hasMany(__CLASS__, ['theme_id' => 'id']);
    }

    /**
     * Gets query for [[Theme]].
     *
     * @return ActiveQuery
     */
    public function getTheme(): ActiveQuery
    {
        return $this->hasOne(__CLASS__, ['id' => 'theme_id']);
    }
}
