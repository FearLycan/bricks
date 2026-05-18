<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * @property int         $id
 * @property int         $user_id
 * @property string      $preference_key
 * @property string      $preference_value
 * @property string      $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class UserPreference extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_preference}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value'      => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'preference_key', 'preference_value'], 'required'],
            [['user_id'], 'integer'],
            [['preference_key'], 'string', 'max' => 60],
            [['preference_value'], 'string', 'max' => 120],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id', 'preference_key', 'preference_value'], 'unique', 'targetAttribute' => ['user_id', 'preference_key', 'preference_value']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Replaces all stored values for a given preference key for a user.
     *
     * @param array<int,string> $values
     */
    public static function setUserPreference(int $userId, string $key, array $values): void
    {
        self::deleteAll(['user_id' => $userId, 'preference_key' => $key]);

        foreach (array_unique($values) as $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }
            $row = new self();
            $row->user_id = $userId;
            $row->preference_key = $key;
            $row->preference_value = $value;
            $row->save(false);
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getUserPreference(int $userId, string $key): array
    {
        return self::find()
            ->select('preference_value')
            ->where(['user_id' => $userId, 'preference_key' => $key])
            ->column();
    }
}
