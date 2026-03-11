<?php

namespace common\models;

use common\enums\StatusEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%store}}".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $url
 * @property string|null $logo
 * @property int $status
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property \common\models\SetOffer[] $setOffers
 */
class Store extends ActiveRecord
{
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

    public static function tableName(): string
    {
        return '{{%store}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 50],
            [['name', 'url', 'logo'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'url' => 'Url',
            'logo' => 'Logo',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSetOffers(): ActiveQuery
    {
        return $this->hasMany(\common\models\SetOffer::class, ['store_id' => 'id']);
    }

    public static function getOrCreate(string $code, string $name): self
    {
        $normalizedCode = strtoupper(trim($code));
        $store = self::findOne(['code' => $normalizedCode]);
        if ($store) {
            if ($name !== '' && $store->name !== $name) {
                $store->name = $name;
                $store->save();
            }

            return $store;
        }

        $store = new self();
        $store->code = $normalizedCode;
        $store->name = $name !== '' ? $name : $normalizedCode;
        $store->save();

        return $store;
    }

    public function isActive(): bool
    {
        return (int)$this->status === StatusEnum::ACTIVE->value;
    }

    public function getStatusLabel(string $defaultText = '-'): string
    {
        return StatusEnum::tryFrom((int)$this->status)?->label() ?? $defaultText;
    }
}
