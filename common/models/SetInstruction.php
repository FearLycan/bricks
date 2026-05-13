<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_instruction}}".
 *
 * @property int         $id
 * @property int         $set_id
 * @property string      $url
 * @property string|null $description
 * @property int         $sort_order
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property Set         $set
 */
class SetInstruction extends ActiveRecord
{
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

    public static function tableName(): string
    {
        return '{{%set_instruction}}';
    }

    public function rules(): array
    {
        return [
            [['set_id', 'url'], 'required'],
            [['set_id', 'sort_order'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['url'], 'string', 'max' => 500],
            [['description'], 'string', 'max' => 255],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
            [['set_id', 'url'], 'unique', 'targetAttribute' => ['set_id', 'url']],
        ];
    }

    public function getSet(): ActiveQuery
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    public static function syncBySet(Set $set, array $results): void
    {
        $syncedIds = [];
        $sortOrder = 0;

        foreach ($results as $item) {
            if (!is_array($item) || !isset($item['URL']) || !is_string($item['URL']) || $item['URL'] === '') {
                continue;
            }

            $url = (string) $item['URL'];

            $model = self::findOne([
                'set_id' => $set->id,
                'url'    => $url,
            ]);

            if (!$model) {
                $model = new self();
                $model->set_id = $set->id;
                $model->url = $url;
            }

            $model->description = isset($item['description']) && $item['description'] !== '' ? (string) $item['description'] : null;
            $model->sort_order = $sortOrder++;
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
