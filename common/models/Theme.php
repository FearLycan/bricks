<?php

namespace common\models;

use common\enums\StatusEnum;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

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
 * @property int         $status
 * @property string|null $description
 * @property string|null $img
 * @property string|null $custom_css
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
                'attribute'     => 'name',
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
        return '{{%theme}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['parent_id', 'group_id', 'sets_count', 'year_from', 'year_to', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['description', 'custom_css'], 'string'],
            [['name', 'img'], 'string', 'max' => 255],
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
            'status' => 'Status',
            'description' => 'Description',
            'img' => 'Img',
            'custom_css' => 'Custom Css',
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

    public static function getOrCreate(string $name, ?ThemeGroup $themeGroup): self
    {
        $theme = self::find()->where(['name' => $name]);
        $theme = $theme->one();

        if (!$theme) {
            $theme = new self();
            $theme->name = $name;
        }

        $theme->group_id = $themeGroup->id ?? null;
        $theme->save();

        return $theme;
    }

    public static function getOrCreateSub(string $name, Theme $theme): self
    {
        $themeSub = self::find()->where([
            'name'      => $name,
            'parent_id' => $theme->id,
        ])->one();

        if (!$themeSub) {
            $themeSub = new self();
            $themeSub->name = $name;
            $themeSub->parent_id = $theme->id;
            $themeSub->save();
        }

        return $themeSub;
    }

    public function isActive(): bool
    {
        return (int)$this->status === StatusEnum::ACTIVE->value;
    }

    public function getStatusLabel(string $defaultText = '-'): string
    {
        return StatusEnum::tryFrom((int)$this->status)?->label() ?? $defaultText;
    }

    public static function getAvailableGroupsList(): array
    {
        $groups = ThemeGroup::find()
            ->select(['id', 'name'])
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();

        $list = [];
        foreach ($groups as $group) {
            $list[(int)$group['id']] = (string)$group['name'];
        }

        return $list;
    }

    public static function getAvailableParentThemesList(?int $excludeId = null): array
    {
        $query = self::find()
            ->select(['id', 'name'])
            ->orderBy(['name' => SORT_ASC])
            ->asArray();

        if ($excludeId !== null) {
            $query->andWhere(['<>', 'id', $excludeId]);
        }

        $themes = $query->all();
        $list = [];
        foreach ($themes as $theme) {
            $list[(int)$theme['id']] = (string)$theme['name'];
        }

        return $list;
    }


    /**
     * @return Theme[]
     */
    public static function getMainThemes(): array
    {
        return self::find()
            ->where('parent_id is null')
            ->andWhere('year_to >= 2020')
            ->orderBy(['id' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
    }
}
