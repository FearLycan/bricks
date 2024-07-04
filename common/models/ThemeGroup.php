<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
            [['name', 'slug'], 'string', 'max' => 255],
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
}
