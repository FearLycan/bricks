<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_minifig}}".
 *
 * @property int         $id
 * @property int         $set_id
 * @property int         $rebrickable_id
 * @property string      $number
 * @property string      $name
 * @property int         $quantity
 * @property string|null $image
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property Set         $set
 */
class SetMinifig extends ActiveRecord
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
        return '{{%set_minifig}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['set_id', 'rebrickable_id', 'number', 'name'], 'required'],
            [['set_id', 'rebrickable_id', 'quantity'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['number'], 'string', 'max' => 30],
            [['name', 'image'], 'string', 'max' => 255],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
            [['set_id', 'rebrickable_id'], 'unique', 'targetAttribute' => ['set_id', 'rebrickable_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'             => 'ID',
            'set_id'         => 'Set ID',
            'rebrickable_id' => 'Rebrickable ID',
            'number'         => 'Number',
            'name'           => 'Name',
            'quantity'       => 'Quantity',
            'image'          => 'Image',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
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

    public static function syncBySet(Set $set, array $results): void
    {
        $syncedIds = [];

        foreach ($results as $item) {
            if (!is_array($item) || !isset($item['id']) || !is_numeric($item['id'])) {
                continue;
            }

            $rebrickableId = (int) $item['id'];
            $model = self::findOne([
                'set_id' => $set->id,
                'rebrickable_id' => $rebrickableId,
            ]);

            if (!$model) {
                $model = new self();
                $model->set_id = $set->id;
                $model->rebrickable_id = $rebrickableId;
            }

            $model->number = (string) ($item['set_num'] ?? '');
            $model->name = (string) ($item['set_name'] ?? '');
            $model->quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;
            $model->image = isset($item['set_img_url']) && $item['set_img_url'] !== '' ? (string) $item['set_img_url'] : null;
            $model->save();

            $syncedIds[] = $model->id;
        }

        if (empty($syncedIds)) {
            self::deleteAll(['set_id' => $set->id]);

            return;
        }

        self::deleteAll(['and', ['set_id' => $set->id], ['not in', 'id', $syncedIds]]);
    }
}
