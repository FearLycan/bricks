<?php

namespace frontend\modules\lego\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\enums\StatusEnum;
use common\models\SearchWizardHash;
use common\models\Set;
use common\models\SetOffer;
use common\models\SetMinifig;
use common\models\SetReview;
use common\models\Tag;
use common\models\User;
use frontend\models\searches\SetSearch;
use Yii;
use yii\data\ActiveDataProvider;

class LegoController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => [
                            'index', 'view', 'minifig', 'offer-reviews-modal', 'promo', 'new', 'tag',
                            'magazines', 'exclusive', 'retiring-soon',
                        ],
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new SetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $wizardData = null;
        $wizardHash = (string)($this->request->get('wizard') ?? '');
        if ($wizardHash !== '') {
            $wizardRecord = SearchWizardHash::findByHash($wizardHash);
            if ($wizardRecord !== null) {
                $wizardRecord->applyToQuery($dataProvider->query);
                $wizardData = $wizardRecord->getPublicData();
                $dataProvider->setTotalCount(null);
                if ($dataProvider->getPagination() !== false) {
                    $dataProvider->getPagination()->totalCount = $dataProvider->getTotalCount();
                }
            }
        }

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'wizardData'   => $wizardData,
            'grouped'      => $searchModel->isMonthGroupedMode(),
        ]);
    }

    public function actionPromo(): string
    {
        $searchModel = new SetSearch();
        $dataProvider = $searchModel->searchPromo();

        return $this->render('promo', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionMagazines(): string
    {
        $searchModel = new SetSearch();
        $params = array_merge($this->request->queryParams, ['availability' => 'Magazine gift']);
        $dataProvider = $searchModel->search($params);

        return $this->render('magazines', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExclusive(): string
    {
        $searchModel = new SetSearch();
        $params = array_merge($this->request->queryParams, ['availability' => 'LEGO exclusive']);
        $dataProvider = $searchModel->search($params);

        return $this->render('exclusive', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRetiringSoon(): string
    {
        $searchModel = new SetSearch();
        $dataProvider = $searchModel->searchRetiringSoon();

        return $this->render('retiring-soon', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTag(string $slug): string
    {
        $tag = Tag::findOne(['slug' => $slug, 'status' => StatusEnum::ACTIVE->value]);
        if (!$tag) {
            $this->notFound();
        }

        $searchModel = new SetSearch();
        $params = array_merge($this->request->queryParams, ['tag_slug' => $slug]);
        $dataProvider = $searchModel->search($params);

        return $this->render('tag', [
            'tag'          => $tag,
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionNew(): \yii\web\Response
    {
        $params = $this->request->queryParams;
        if (isset($params['new_page'])) {
            $params['page'] = $params['new_page'];
            unset($params['new_page']);
        }

        return $this->redirect(array_merge(['/lego'], $params), 301);
    }

    public function actionView(string $slug): string
    {
        $model = $this->findModel($slug);
        $identity = $this->user->identity;

        $reviewStats = SetReview::getSetStats((int)$model->id);
        $reviewList = SetReview::find()
            ->with(['user', 'scores', 'answers'])
            ->where(['set_id' => (int)$model->id, 'status' => SetReview::STATUS_PUBLISHED])
            ->orderBy(['published_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(10)
            ->all();

        $userReview = null;
        $userMatchScore = null;
        if (!Yii::$app->user->isGuest) {
            $userReview = SetReview::findByUserAndSet((int)Yii::$app->user->id, (int)$model->id);
            $userMatchScore = SetReview::getMatchScoreForSet((int)Yii::$app->user->id, (int)$model->id);
        }

        return $this->render('view', [
            'model'          => $model,
            'user'           => $identity instanceof User ? $identity : null,
            'reviewStats'    => $reviewStats,
            'reviewList'     => $reviewList,
            'userReview'     => $userReview,
            'userMatchScore' => $userMatchScore,
        ]);
    }

    public function actionMinifig(string $number): string
    {
        $query = Set::find()
            ->alias('set')
            ->innerJoin(SetMinifig::tableName() . ' sm', 'sm.set_id = set.id')
            ->where(['sm.number' => $number])
            ->groupBy('set.id')
            ->orderBy(['set.year' => SORT_DESC, 'set.id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 24],
        ]);

        $minifigPreview = SetMinifig::find()
            ->select(['name', 'image'])
            ->where(['number' => $number])
            ->asArray()
            ->one();

        $minifigName = (string)($minifigPreview['name'] ?? '');
        $minifigImage = isset($minifigPreview['image']) ? (string)$minifigPreview['image'] : '';

        return $this->render('minifig', [
            'dataProvider' => $dataProvider,
            'number'       => $number,
            'name'         => $minifigName,
            'image'        => $minifigImage,
        ]);
    }

    public function actionOfferReviewsModal(int $setOfferId): string
    {
        $offer = SetOffer::find()
            ->with(['setOfferReviews.setOfferReviewImages', 'store'])
            ->where(['id' => $setOfferId])
            ->one();

        if (!$offer) {
            $this->notFound();
        }

        $averageRating = $offer->getDisplayRatingValue();
        $reviewsTotal = $offer->getDisplayReviewCount();
        $ratingStarClasses = $offer->getRatingStarClasses($averageRating);
        $reviewImpressions = $offer->getReviewImpressions();

        return $this->renderAjax('_offer-reviews-modal', [
            'offer'             => $offer,
            'averageRating'     => $averageRating,
            'reviewsTotal'      => $reviewsTotal,
            'ratingStarClasses' => $ratingStarClasses,
            'reviewImpressions' => $reviewImpressions,
        ]);
    }

    private function findModel(string $slug): Set
    {
        $conditions = ['or', ['slug' => $slug]];
        if (ctype_digit($slug)) {
            $conditions[] = ['number' => (int)$slug];
        }

        $model = Set::find()
            ->with([
                'setOffers.store',
                'setOffers.setOfferReviews',
            ])
            ->where($conditions)
            ->one();

        if (!$model) {
            $this->notFound();
        }

        return $model;
    }
}