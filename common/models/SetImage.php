<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%set_image}}".
 *
 * @property int         $id
 * @property string      $url
 * @property int         $set_id
 * @property string      $type
 * @property int|null    $status
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property Set         $set
 */
class SetImage extends ActiveRecord
{
    /*public const TYPE_SCREENSHOT = 'screenshot';
    public const TYPE_ICON       = 'icon';
    public const TYPE_BACKGROUND = 'background';
    public const TYPE_HEADER     = 'header';*/

    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%set_image}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['url', 'set_id', 'type'], 'required'],
            [['set_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['url', 'type'], 'string', 'max' => 255],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'url'        => 'Url',
            'set_id'     => 'Set ID',
            'type'       => 'Type',
            'status'     => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
}
