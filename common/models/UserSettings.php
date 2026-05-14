<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * @property int         $user_id
 * @property int         $hide_owned_sets
 * @property string|null $preferred_language
 * @property string      $created_at
 * @property string      $updated_at
 *
 * @property User $user
 */
class UserSettings extends ActiveRecord
{
    public const SUPPORTED_LANGUAGES = ['en', 'pl', 'de', 'fr', 'es', 'it', 'ja', 'zh'];

    public static function tableName(): string
    {
        return '{{%user_settings}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value'      => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'hide_owned_sets'], 'integer'],
            [['hide_owned_sets'], 'boolean'],
            [['hide_owned_sets'], 'default', 'value' => 0],
            [['preferred_language'], 'string', 'max' => 5],
            [['preferred_language'], 'in', 'range' => self::SUPPORTED_LANGUAGES, 'strict' => true, 'skipOnEmpty' => true],
            [['preferred_language'], 'default', 'value' => null],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['user_id'], 'unique'],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
