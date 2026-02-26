<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_tag}}".
 *
 * @property int $id
 * @property int $set_id
 * @property int $tag_id
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Set $set
 * @property Tag $tag
 */
class SetTag extends ActiveRecord
{
    /**
     * @return array
     */
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

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%set_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['set_id', 'tag_id'], 'required'],
            [['set_id', 'tag_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['set_id', 'tag_id'], 'unique', 'targetAttribute' => ['set_id', 'tag_id']],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::class, 'targetAttribute' => ['tag_id' => 'id']],
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

    /**
     * Gets query for [[Tag]].
     *
     * @return ActiveQuery
     */
    public function getTag(): ActiveQuery
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }

    public static function syncBySetAndNames(Set $set, array $tagNames): void
    {
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            if (!is_string($tagName)) {
                continue;
            }

            $tagName = trim($tagName);
            if ($tagName === '') {
                continue;
            }

            $tag = Tag::getOrCreate($tagName);
            $tagIds[] = $tag->id;
        }

        $tagIds = array_values(array_unique($tagIds));

        if (empty($tagIds)) {
            self::deleteAll(['set_id' => $set->id]);

            return;
        }

        self::deleteAll(['and', ['set_id' => $set->id], ['not in', 'tag_id', $tagIds]]);

        foreach ($tagIds as $tagId) {
            $exists = self::findOne([
                'set_id' => $set->id,
                'tag_id' => $tagId,
            ]);

            if ($exists) {
                continue;
            }

            $setTag = new self();
            $setTag->set_id = $set->id;
            $setTag->tag_id = $tagId;
            $setTag->save();
        }
    }
}
