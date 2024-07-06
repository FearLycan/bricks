<?php

namespace common\models;

use common\enums\image\TypeEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

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

    public static function getOrCreate(Set $set, TypeEnum $type, string $url): self
    {
        $image = self::findOne(['type' => $type->value, 'set_id' => $set->id]);
        if (!$image) {
            $image = new self();
            $image->type = $type->value;
            $image->set_id = $set->id;
        }

        $image->url = $url;
        $image->save();

        return $image;
    }
}
