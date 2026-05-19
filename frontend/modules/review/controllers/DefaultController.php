<?php

namespace frontend\modules\review\controllers;

use common\components\Controller;
use common\models\OwnedSet;
use common\models\Set;
use common\models\SetReview;
use common\models\SetReviewAnswer;
use common\models\SetReviewScore;
use frontend\components\T;
use Yii;
use yii\db\Exception as DbException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['choice-modal', 'simple-modal', 'detailed-modal', 'login-prompt-modal'],
                        'roles'   => ['?', '@'],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['save-simple', 'save-detailed', 'delete'],
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'save-simple'   => ['post'],
                    'save-detailed' => ['post'],
                    'delete'        => ['post'],
                ],
            ],
        ];
    }

    public function actionChoiceModal(int $setId): string
    {
        $set = $this->findSet($setId);

        if (Yii::$app->user->isGuest) {
            return $this->renderAjax('_login-prompt-modal', [
                'set' => $set,
            ]);
        }

        $userId = (int)Yii::$app->user->id;
        $existing = SetReview::findByUserAndSet($userId, (int)$set->id);

        return $this->renderAjax('_choice-modal', [
            'set'      => $set,
            'existing' => $existing,
        ]);
    }

    public function actionLoginPromptModal(int $setId): string
    {
        $set = $this->findSet($setId);

        return $this->renderAjax('_login-prompt-modal', [
            'set' => $set,
        ]);
    }

    public function actionSimpleModal(int $setId): string
    {
        $set = $this->findSet($setId);

        if (Yii::$app->user->isGuest) {
            return $this->renderAjax('_login-prompt-modal', [
                'set' => $set,
            ]);
        }

        $userId = (int)Yii::$app->user->id;
        $existing = SetReview::findByUserAndSet($userId, (int)$set->id);
        $ownsSet = OwnedSet::isOwnedByCurrentUser((int)$set->id);

        return $this->renderAjax('_simple-modal', [
            'set'      => $set,
            'existing' => $existing,
            'ownsSet'  => $ownsSet,
        ]);
    }

    public function actionDetailedModal(int $setId): string
    {
        $set = $this->findSet($setId);

        if (Yii::$app->user->isGuest) {
            return $this->renderAjax('_login-prompt-modal', [
                'set' => $set,
            ]);
        }

        $userId = (int)Yii::$app->user->id;
        $existing = SetReview::findByUserAndSet($userId, (int)$set->id);
        $ownsSet = OwnedSet::isOwnedByCurrentUser((int)$set->id);

        return $this->renderAjax('_detailed-modal', [
            'set'      => $set,
            'existing' => $existing,
            'ownsSet'  => $ownsSet,
        ]);
    }

    public function actionSaveSimple(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $payload = $this->readJsonBody();
        $setId = (int)($payload['set_id'] ?? 0);
        $set = $this->findSet($setId);

        $score = SetReview::normalizeScore($payload['overall_score'] ?? null);
        if ($score === null) {
            throw new BadRequestHttpException(T::tr('Please provide a rating between 1 and 10.'));
        }

        $userId = (int)Yii::$app->user->id;

        $this->maybeAddToOwned((int)$set->id, $userId, (bool)($payload['owns_set'] ?? false));

        $review = SetReview::findByUserAndSet($userId, (int)$set->id) ?? new SetReview();
        $isNew = $review->isNewRecord;
        if ($isNew) {
            $review->user_id = $userId;
            $review->set_id  = (int)$set->id;
        }

        $review->review_type   = SetReview::TYPE_SIMPLE;
        $review->overall_score = $score;
        $review->title         = $this->cleanString($payload['title'] ?? null, 255);
        $review->content       = $this->cleanString($payload['content'] ?? null);
        $review->publish();

        if (!$review->save()) {
            return [
                'success' => false,
                'message' => T::tr('Could not save your rating. Please try again.'),
                'errors'  => $review->getFirstErrors(),
            ];
        }

        SetReviewScore::deleteAll(['set_review_id' => $review->id]);
        SetReviewAnswer::deleteAll(['set_review_id' => $review->id]);

        SetReview::refreshSetRating((int)$set->id);

        return [
            'success'     => true,
            'message'     => $isNew ? T::tr('Thanks for your review!') : T::tr('Your review has been updated.'),
            'redirectUrl' => $this->buildSetUrlWithReviews($set),
        ];
    }

    public function actionSaveDetailed(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $payload = $this->readJsonBody();
        $setId = (int)($payload['set_id'] ?? 0);
        $set = $this->findSet($setId);

        $userId = (int)Yii::$app->user->id;

        $rawScores = is_array($payload['scores'] ?? null) ? $payload['scores'] : [];
        $scoresMap = [];
        foreach (array_keys(SetReview::DIMENSIONS) as $dimensionKey) {
            $normalized = SetReview::normalizeScore($rawScores[$dimensionKey] ?? null);
            if ($normalized === null) {
                throw new BadRequestHttpException(T::tr('Please rate every dimension before submitting.'));
            }
            $scoresMap[$dimensionKey] = $normalized;
        }

        $overall = SetReview::normalizeScore(array_sum($scoresMap) / max(1, count($scoresMap)));

        $this->maybeAddToOwned((int)$set->id, $userId, (bool)($payload['owns_set'] ?? false));

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $review = SetReview::findByUserAndSet($userId, (int)$set->id) ?? new SetReview();
            $isNew = $review->isNewRecord;
            if ($isNew) {
                $review->user_id = $userId;
                $review->set_id  = (int)$set->id;
            }

            $review->review_type   = SetReview::TYPE_DETAILED;
            $review->overall_score = $overall;
            $review->title         = $this->cleanString($payload['title'] ?? null, 255);
            $review->content       = $this->cleanString($payload['content'] ?? null);
            $review->publish();

            if (!$review->save()) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => T::tr('Could not save your review. Please try again.'),
                    'errors'  => $review->getFirstErrors(),
                ];
            }

            SetReviewScore::deleteAll(['set_review_id' => $review->id]);
            foreach ($scoresMap as $dimensionKey => $score) {
                $row = new SetReviewScore();
                $row->set_review_id = (int)$review->id;
                $row->dimension_key = $dimensionKey;
                $row->score = $score;
                $row->save(false);
            }

            SetReviewAnswer::deleteAll(['set_review_id' => $review->id]);
            $rawAnswers = is_array($payload['answers'] ?? null) ? $payload['answers'] : [];
            foreach (SetReview::QUESTIONS_BY_DIMENSION as $questions) {
                foreach ($questions as $question) {
                    $key = $question['key'];
                    if (!array_key_exists($key, $rawAnswers)) {
                        continue;
                    }
                    $value = $rawAnswers[$key];
                    if ($value === null || $value === '' || $value === []) {
                        continue;
                    }

                    if ($question['type'] === 'text') {
                        $row = new SetReviewAnswer();
                        $row->set_review_id = (int)$review->id;
                        $row->question_key = $key;
                        $row->answer_text = $this->cleanString((string)$value, 500);
                        $row->save(false);
                        continue;
                    }

                    $allowed = $question['options'] ?? [];
                    if (!in_array((string)$value, $allowed, true)) {
                        continue;
                    }

                    $row = new SetReviewAnswer();
                    $row->set_review_id = (int)$review->id;
                    $row->question_key = $key;
                    $row->answer_value = (string)$value;
                    $row->save(false);
                }
            }

            // Preference questions (multi-select) are stored as multiple rows in
            // set_review_answer for THIS review, so preferences can vary per set
            // (e.g. one set is for display, another for play). An aggregated global
            // taste profile can be derived from all of the user's reviews later.
            $rawPreferences = is_array($payload['preferences'] ?? null) ? $payload['preferences'] : [];
            foreach (SetReview::PREFERENCE_QUESTIONS as $prefQuestion) {
                $key = $prefQuestion['key'];
                if (!array_key_exists($key, $rawPreferences)) {
                    continue;
                }
                $values = $rawPreferences[$key];
                if (!is_array($values)) {
                    $values = [$values];
                }
                $allowed = $prefQuestion['options'];
                $filtered = array_values(array_unique(array_filter(
                    array_map(static fn($v) => (string)$v, $values),
                    static fn($v) => in_array($v, $allowed, true)
                )));
                if (isset($prefQuestion['max_select'])) {
                    $filtered = array_slice($filtered, 0, (int)$prefQuestion['max_select']);
                }
                foreach ($filtered as $value) {
                    $row = new SetReviewAnswer();
                    $row->set_review_id = (int)$review->id;
                    $row->question_key = $key;
                    $row->answer_value = $value;
                    $row->save(false);
                }
            }

            SetReview::refreshSetRating((int)$set->id);

            $transaction->commit();
        } catch (DbException $exception) {
            $transaction->rollBack();
            Yii::error('Failed to save detailed review: ' . $exception->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => T::tr('Could not save your review. Please try again.'),
            ];
        }

        return [
            'success'     => true,
            'message'     => $isNew ? T::tr('Thanks for your detailed review!') : T::tr('Your detailed review has been updated.'),
            'redirectUrl' => $this->buildSetUrlWithReviews($set),
        ];
    }

    public function actionDelete(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $setId = (int)Yii::$app->request->post('set_id', 0);
        $set = $this->findSet($setId);
        $userId = (int)Yii::$app->user->id;

        $review = SetReview::findByUserAndSet($userId, (int)$set->id);
        if ($review !== null) {
            $review->delete();
            SetReview::refreshSetRating((int)$set->id);
        }

        return [
            'success'     => true,
            'message'     => T::tr('Your review has been removed.'),
            'redirectUrl' => $this->buildSetUrlWithReviews($set, false),
        ];
    }

    private function findSet(int $setId): Set
    {
        if ($setId <= 0) {
            throw new BadRequestHttpException(T::tr('Invalid set.'));
        }
        $set = Set::findOne($setId);
        if (!$set) {
            throw new NotFoundHttpException(T::tr('Set not found.'));
        }
        return $set;
    }

    private function maybeAddToOwned(int $setId, int $userId, bool $ownsSet): void
    {
        if (!$ownsSet) {
            return;
        }
        if (OwnedSet::find()->where(['user_id' => $userId, 'set_id' => $setId])->exists()) {
            return;
        }
        $row = new OwnedSet();
        $row->user_id = $userId;
        $row->set_id = $setId;
        $row->save(false);
        OwnedSet::invalidateCurrentUserCache();
    }

    private function readJsonBody(): array
    {
        $body = Yii::$app->request->getRawBody();
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new BadRequestHttpException(T::tr('Invalid request body'));
        }
        return $data;
    }

    private function cleanString(mixed $value, ?int $maxLength = null): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        if ($maxLength !== null && mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }
        return $value;
    }

    private function buildSetUrlWithReviews(Set $set, bool $jumpToReviews = true): string
    {
        $url = Url::to(['/lego/lego/view', 'slug' => $set->slug]);
        return $jumpToReviews ? $url . '#reviews' : $url;
    }
}
