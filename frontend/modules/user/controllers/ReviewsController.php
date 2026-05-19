<?php

namespace frontend\modules\user\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\SetReview;
use frontend\components\T;
use Yii;
use yii\data\ActiveDataProvider;

class ReviewsController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index'],
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $userId = (int)Yii::$app->user->id;

        $dataProvider = new ActiveDataProvider([
            'query'      => SetReview::find()
                ->alias('sr')
                ->with(['set', 'set.images'])
                ->where(['sr.user_id' => $userId])
                ->orderBy(['sr.published_at' => SORT_DESC, 'sr.created_at' => SORT_DESC, 'sr.id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        $dataProvider->prepare();

        return $this->render('index', [
            'dataProvider'         => $dataProvider,
            'reviews'              => $dataProvider->getModels(),
            'totalCount'           => (int)$dataProvider->getTotalCount(),
            'dimensionShortLabels' => [
                'design'           => T::tr('Look'),
                'build_experience' => T::tr('Build'),
                'playability'      => T::tr('Features'),
                'quality'          => T::tr('Quality'),
                'value'            => T::tr('Value'),
                'recommendation'   => T::tr('Overall'),
            ],
        ]);
    }
}
