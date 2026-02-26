<?php

namespace common\models;

use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%tag}}".
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property \common\models\SetTag[] $setTags
 */
class Tag extends ActiveRecord
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
            'sluggable' => [
                'class' => SluggableBehavior::class,
                'attribute' => ['name'],
                'slugAttribute' => 'slug',
                'ensureUnique' => false,
                'immutable' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[SetTags]].
     *
     * @return ActiveQuery
     */
    public function getSetTags(): ActiveQuery
    {
        return $this->hasMany(\common\models\SetTag::class, ['tag_id' => 'id']);
    }

    public static function getOrCreate(string $name): self
    {
        $normalizedName = trim($name);
        if ($normalizedName === '') {
            throw new \InvalidArgumentException('Tag name cannot be empty.');
        }

        $tag = self::findOne(['name' => $normalizedName]);
        if ($tag) {
            return $tag;
        }

        $tag = new self();
        $tag->name = $normalizedName;
        $tag->save();

        return $tag;
    }
}
