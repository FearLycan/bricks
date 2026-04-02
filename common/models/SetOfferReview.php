<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%set_offer_review}}".
 *
 * @property int $id
 * @property int $set_offer_id
 * @property string|null $external_review_id
 * @property string|null $author_name
 * @property string|null $reviewer_country
 * @property string|null $title
 * @property string|null $content
 * @property float|null $rating_value
 * @property float|null $rating_scale_max
 * @property string|null $reviewed_at
 * @property string|null $source
 * @property int $is_manual_override
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property SetOffer $setOffer
 * @property SetOfferReviewImage[] $setOfferReviewImages
 */
class SetOfferReview extends ActiveRecord
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
        return '{{%set_offer_review}}';
    }

    public function rules(): array
    {
        return [
            [['set_offer_id'], 'required'],
            [['set_offer_id', 'is_manual_override'], 'integer'],
            [['content'], 'string'],
            [['rating_value', 'rating_scale_max'], 'number'],
            [['reviewed_at', 'created_at', 'updated_at'], 'safe'],
            [['external_review_id'], 'string', 'max' => 100],
            [['reviewer_country'], 'string', 'max' => 10],
            [['author_name', 'title'], 'string', 'max' => 255],
            [['source'], 'string', 'max' => 50],
            [['set_offer_id', 'external_review_id'], 'unique', 'targetAttribute' => ['set_offer_id', 'external_review_id']],
            [['set_offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => SetOffer::class, 'targetAttribute' => ['set_offer_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'set_offer_id' => 'Set Offer ID',
            'external_review_id' => 'External Review ID',
            'author_name' => 'Author Name',
            'reviewer_country' => 'Reviewer Country',
            'title' => 'Title',
            'content' => 'Content',
            'rating_value' => 'Rating Value',
            'rating_scale_max' => 'Rating Scale Max',
            'reviewed_at' => 'Reviewed At',
            'source' => 'Source',
            'is_manual_override' => 'Is Manual Override',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSetOffer(): ActiveQuery
    {
        return $this->hasOne(SetOffer::class, ['id' => 'set_offer_id']);
    }

    public function getSetOfferReviewImages(): ActiveQuery
    {
        return $this->hasMany(SetOfferReviewImage::class, ['set_offer_review_id' => 'id'])->orderBy(['position' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public static function syncByOffer(SetOffer $setOffer, array $items, string $source): void
    {
        $syncedIds = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $externalReviewId = isset($item['external_review_id']) ? (string)$item['external_review_id'] : null;
            $query = self::find()->where(['set_offer_id' => $setOffer->id]);
            if ($externalReviewId !== null && $externalReviewId !== '') {
                $query->andWhere(['external_review_id' => $externalReviewId]);
            } else {
                $query->andWhere([
                    'title' => isset($item['title']) ? (string)$item['title'] : null,
                    'author_name' => isset($item['author_name']) ? (string)$item['author_name'] : null,
                ]);
            }

            $review = $query->one();
            if (!$review) {
                $review = new self();
                $review->set_offer_id = $setOffer->id;
            }

            if ((int)$review->is_manual_override === 1) {
                $syncedIds[] = $review->id;
                continue;
            }

            $review->external_review_id = $externalReviewId ?: null;
            $review->author_name = self::sanitizeForStorage(isset($item['author_name']) ? (string)$item['author_name'] : null);
            $review->reviewer_country = isset($item['reviewer_country']) ? (string)$item['reviewer_country'] : null;
            $review->title = self::sanitizeForStorage(isset($item['title']) ? (string)$item['title'] : null);
            $review->content = self::sanitizeForStorage(isset($item['content']) ? (string)$item['content'] : null);
            $review->rating_value = isset($item['rating_value']) && is_numeric($item['rating_value']) ? (float)$item['rating_value'] : null;
            $review->rating_scale_max = isset($item['rating_scale_max']) && is_numeric($item['rating_scale_max']) ? (float)$item['rating_scale_max'] : 5.0;
            $review->reviewed_at = isset($item['reviewed_at']) && is_string($item['reviewed_at']) ? $item['reviewed_at'] : null;
            $review->source = $source;
            $review->save();

            $images = isset($item['images']) && is_array($item['images']) ? $item['images'] : [];
            SetOfferReviewImage::syncByReview($review, $images);

            $syncedIds[] = $review->id;
        }

        if ($syncedIds === []) {
            return;
        }

        self::deleteAll([
            'and',
            ['set_offer_id' => $setOffer->id, 'is_manual_override' => 0],
            ['not in', 'id', $syncedIds],
        ]);

        self::refreshOfferRatingSummary($setOffer);
    }

    private static function sanitizeForStorage(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $sanitized = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $value) ?? $value;
        $asciiSafe = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $sanitized);
        if (is_string($asciiSafe) && $asciiSafe !== '') {
            $sanitized = $asciiSafe;
        }

        $sanitized = preg_replace('/[^\x20-\x7E\r\n\t]/', '', $sanitized) ?? $sanitized;

        return trim($sanitized) !== '' ? $sanitized : null;
    }

    private static function refreshOfferRatingSummary(SetOffer $setOffer): void
    {
        $reviewCount = (int)self::find()
            ->where(['set_offer_id' => $setOffer->id])
            ->count();

        $ratingRows = self::find()
            ->select(['rating_value'])
            ->where(['set_offer_id' => $setOffer->id])
            ->andWhere(['not', ['rating_value' => null]])
            ->asArray()
            ->all();

        $ratingValue = null;
        if ($ratingRows !== []) {
            $sum = 0.0;
            foreach ($ratingRows as $row) {
                $sum += (float)($row['rating_value'] ?? 0.0);
            }
            $ratingValue = round($sum / count($ratingRows), 1);
        }

        $setOffer->review_count = $reviewCount;
        $setOffer->rating_value = $ratingValue;
        $setOffer->rating_scale_max = $ratingValue !== null ? 5.0 : null;
        $setOffer->save(false, ['review_count', 'rating_value', 'rating_scale_max']);
    }
}
