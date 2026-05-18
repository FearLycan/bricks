<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $set_review_id
 * @property string $dimension_key
 * @property float  $score
 *
 * @property SetReview $review
 */
class SetReviewScore extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%set_review_score}}';
    }

    public function rules(): array
    {
        return [
            [['set_review_id', 'dimension_key', 'score'], 'required'],
            [['set_review_id'], 'integer'],
            [['score'], 'number', 'min' => SetReview::SCORE_MIN, 'max' => SetReview::SCORE_MAX],
            [['dimension_key'], 'string', 'max' => 50],
            [['dimension_key'], 'in', 'range' => array_keys(SetReview::DIMENSIONS)],
            [['set_review_id', 'dimension_key'], 'unique', 'targetAttribute' => ['set_review_id', 'dimension_key']],
            [['set_review_id'], 'exist', 'skipOnError' => true, 'targetClass' => SetReview::class, 'targetAttribute' => ['set_review_id' => 'id']],
        ];
    }

    public function getReview(): ActiveQuery
    {
        return $this->hasOne(SetReview::class, ['id' => 'set_review_id']);
    }
}
