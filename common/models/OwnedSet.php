<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%owned_set}}".
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $set_id
 * @property string $created_at
 *
 * @property User $user
 * @property Set  $set
 */
class OwnedSet extends ActiveRecord
{
    /** @var array<int, array<int, true>> Cache of user_id => [set_id => true] */
    private static array $userSetIdsCache = [];

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value'      => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public static function tableName(): string
    {
        return '{{%owned_set}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'set_id'], 'required'],
            [['user_id', 'set_id'], 'integer'],
            [['created_at'], 'safe'],
            [['user_id', 'set_id'], 'unique', 'targetAttribute' => ['user_id', 'set_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getSet(): ActiveQuery
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    /**
     * Returns all set IDs owned by the current user, cached per request.
     *
     * @return array<int, true>
     */
    public static function getCurrentUserSetIds(): array
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        $userId = (int)Yii::$app->user->id;
        if (isset(self::$userSetIdsCache[$userId])) {
            return self::$userSetIdsCache[$userId];
        }

        $setIds = self::find()
            ->alias('o')
            ->select('o.set_id')
            ->where(['o.user_id' => $userId])
            ->column();

        $map = [];
        foreach ($setIds as $setId) {
            $map[(int)$setId] = true;
        }

        return self::$userSetIdsCache[$userId] = $map;
    }

    public static function isOwnedByCurrentUser(int $setId): bool
    {
        return isset(self::getCurrentUserSetIds()[$setId]);
    }

    public static function invalidateCurrentUserCache(): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        unset(self::$userSetIdsCache[(int)Yii::$app->user->id]);
    }
}
