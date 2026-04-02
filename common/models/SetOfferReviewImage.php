<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_offer_review_image}}".
 *
 * @property int $id
 * @property int $set_offer_review_id
 * @property string $url
 * @property int $position
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property SetOfferReview $setOfferReview
 */
class SetOfferReviewImage extends ActiveRecord
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
        return '{{%set_offer_review_image}}';
    }

    public function rules(): array
    {
        return [
            [['set_offer_review_id', 'url'], 'required'],
            [['set_offer_review_id', 'position'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['url'], 'string', 'max' => 255],
            [['set_offer_review_id', 'url'], 'unique', 'targetAttribute' => ['set_offer_review_id', 'url']],
            [['set_offer_review_id'], 'exist', 'skipOnError' => true, 'targetClass' => SetOfferReview::class, 'targetAttribute' => ['set_offer_review_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'set_offer_review_id' => 'Set Offer Review ID',
            'url' => 'Url',
            'position' => 'Position',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSetOfferReview(): ActiveQuery
    {
        return $this->hasOne(SetOfferReview::class, ['id' => 'set_offer_review_id']);
    }

    public static function syncByReview(SetOfferReview $review, array $images): void
    {
        $syncedIds = [];
        $position = 0;
        foreach ($images as $url) {
            if (!is_string($url)) {
                continue;
            }

            $normalizedUrl = trim($url);
            if ($normalizedUrl === '') {
                continue;
            }

            $image = self::findOne([
                'set_offer_review_id' => $review->id,
                'url' => $normalizedUrl,
            ]);
            if (!$image) {
                $image = new self();
                $image->set_offer_review_id = $review->id;
                $image->url = $normalizedUrl;
            }

            $image->position = $position;
            $image->save();
            $syncedIds[] = $image->id;
            $position++;
        }

        if ($syncedIds === []) {
            self::deleteAll(['set_offer_review_id' => $review->id]);
            return;
        }

        self::deleteAll([
            'and',
            ['set_offer_review_id' => $review->id],
            ['not in', 'id', $syncedIds],
        ]);
    }
}
