<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%theme}}".
 *
 * @property int         $id
 * @property string|null $name
 * @property string|null $slug
 * @property int|null    $parent_id
 * @property int|null    $group_id
 * @property int|null    $sets_count
 * @property int|null    $year_from
 * @property int|null    $year_to
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property ThemeGroup  $group
 * @property Theme       $parent
 * @property Theme[]     $subThemes
 */
class Theme extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%theme}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['parent_id', 'group_id', 'sets_count', 'year_from', 'year_to'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => ThemeGroup::class, 'targetAttribute' => ['group_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => __CLASS__, 'targetAttribute' => ['parent_id' => 'id']],
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
            'parent_id'  => 'Parent ID',
            'group_id'   => 'Group ID',
            'sets_count' => 'Sets Count',
            'year_from'  => 'Year From',
            'year_to'    => 'Year To',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return ActiveQuery
     */
    public function getGroup(): ActiveQuery
    {
        return $this->hasOne(ThemeGroup::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        return $this->hasOne(__CLASS__, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Themes]].
     *
     * @return ActiveQuery
     */
    public function getSubThemes(): ActiveQuery
    {
        return $this->hasMany(__CLASS__, ['parent_id' => 'id']);
    }
}
