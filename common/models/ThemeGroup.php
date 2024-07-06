<?php

namespace common\models;

use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%theme_group}}".
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $slug
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property Theme[]     $themes
 */
class ThemeGroup extends ActiveRecord
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
                'attribute'     => ['name'],
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
        return '{{%theme_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'name'       => 'Name',
            'slug'       => 'Slug',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Themes]].
     *
     * @return ActiveQuery
     */
    public function getThemes(): ActiveQuery
    {
        return $this->hasMany(Theme::class, ['group_id' => 'id']);
    }

    public static function getOrCreate(string $name): self
    {
        $group = self::find()->where([
            'name' => $name,
        ])->one();

        if (!$group) {
            $group = new self();
            $group->name = $name;
            $group->save();
        }

        return $group;
    }
}
