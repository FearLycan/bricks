<?php

namespace frontend\modules\lego\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\Set;
use common\models\SetOffer;
use common\models\SetMinifig;
use common\models\User;
use frontend\models\searches\SetSearch;
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
                            'index', 'view', 'minifig', 'offer-reviews-modal',
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(string $slug): string
    {
        $model = $this->findModel($slug);
        $identity = $this->user->identity;

        return $this->render('view', [
            'model' => $model,
            'user' => $identity instanceof User ? $identity : null,
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
            'query' => $query,
            'pagination' => ['pageSize' => 24],
        ]);

        $minifigPreview = SetMinifig::find()
            ->select(['name', 'image'])
            ->where(['number' => $number])
            ->asArray()
            ->one();

        $minifigName = (string) ($minifigPreview['name'] ?? '');
        $minifigImage = isset($minifigPreview['image']) ? (string) $minifigPreview['image'] : '';

        return $this->render('minifig', [
            'dataProvider' => $dataProvider,
            'number' => $number,
            'name' => $minifigName,
            'image' => $minifigImage,
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
            'offer' => $offer,
            'averageRating' => $averageRating,
            'reviewsTotal' => $reviewsTotal,
            'ratingStarClasses' => $ratingStarClasses,
            'reviewImpressions' => $reviewImpressions,
        ]);
    }

    private function findModel(string $slug): Set
    {
        $model = Set::find()
            ->with([
                'setOffers.store',
                'setOffers.setOfferReviews',
            ])
            ->where(['slug' => $slug])
            ->one();

        if (!$model) {
            $this->notFound();
        }

        return $model;
    }
}