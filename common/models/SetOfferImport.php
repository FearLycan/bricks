<?php

namespace common\models;

use common\enums\SetOfferImportStatusEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * @property int           $id
 * @property int           $set_id
 * @property string        $input_url
 * @property string        $status
 * @property string|null   $error_message
 * @property int           $attempts
 * @property int|null      $set_offer_id
 * @property string|null   $processed_at
 * @property string        $created_at
 * @property string|null   $updated_at
 *
 * @property Set           $set
 * @property-read string   $statusLabel
 * @property SetOffer|null $setOffer
 */
class SetOfferImport extends ActiveRecord
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
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public static function tableName(): string
    {
        return '{{%set_offer_import}}';
    }

    public function rules(): array
    {
        return [
            [['set_id', 'input_url', 'status'], 'required'],
            [['set_id', 'attempts', 'set_offer_id'], 'integer'],
            [['processed_at', 'created_at', 'updated_at'], 'safe'],
            [['input_url'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
            [['error_message'], 'string', 'max' => 1000],
            [['status'], 'in', 'range' => array_keys(SetOfferImportStatusEnum::options())],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
            [['set_offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => SetOffer::class, 'targetAttribute' => ['set_offer_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'set_id' => 'Set ID',
            'input_url' => 'Input URL',
            'status' => 'Status',
            'error_message' => 'Error Message',
            'attempts' => 'Attempts',
            'set_offer_id' => 'Set Offer ID',
            'processed_at' => 'Processed At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSet(): ActiveQuery
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    public function getSetOffer(): ActiveQuery
    {
        return $this->hasOne(SetOffer::class, ['id' => 'set_offer_id']);
    }

    public function getStatusLabel(): string
    {
        return SetOfferImportStatusEnum::tryFrom((string)$this->status)?->label() ?? ucfirst((string)$this->status);
    }
}
