<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int         $id
 * @property int         $set_review_id
 * @property string      $question_key
 * @property string|null $answer_value
 * @property string|null $answer_text
 *
 * @property SetReview $review
 */
class SetReviewAnswer extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%set_review_answer}}';
    }

    public function rules(): array
    {
        return [
            [['set_review_id', 'question_key'], 'required'],
            [['set_review_id'], 'integer'],
            [['answer_text'], 'string'],
            [['question_key'], 'string', 'max' => 60],
            [['answer_value'], 'string', 'max' => 120],
            [['set_review_id'], 'exist', 'skipOnError' => true, 'targetClass' => SetReview::class, 'targetAttribute' => ['set_review_id' => 'id']],
        ];
    }

    public function getReview(): ActiveQuery
    {
        return $this->hasOne(SetReview::class, ['id' => 'set_review_id']);
    }
}
