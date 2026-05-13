<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\Set;
use common\models\Wishlist;
use frontend\components\T;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class WishlistController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index', 'toggle', 'remove'],
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'toggle' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $userId = (int)Yii::$app->user->id;

        $query = Wishlist::find()
            ->alias('w')
            ->innerJoinWith(['set s'], false)
            ->with(['set.mainImageRelation'])
            ->where(['w.user_id' => $userId])
            ->orderBy(['w.created_at' => SORT_DESC, 'w.id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 24],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionToggle(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $setId = (int)Yii::$app->request->post('set_id', 0);
        if ($setId <= 0) {
            throw new BadRequestHttpException('Invalid set.');
        }

        $set = Set::findOne($setId);
        if (!$set) {
            throw new BadRequestHttpException('Set not found.');
        }

        $userId = (int)Yii::$app->user->id;
        $existing = Wishlist::findOne(['user_id' => $userId, 'set_id' => $setId]);

        if ($existing) {
            $existing->delete();
            Wishlist::invalidateCurrentUserCache();

            return [
                'success'      => true,
                'in_wishlist'  => false,
                'message'      => T::tr('Removed from wishlist.'),
            ];
        }

        $entry = new Wishlist();
        $entry->user_id = $userId;
        $entry->set_id = $setId;
        if (!$entry->save()) {
            return [
                'success' => false,
                'message' => T::tr('Could not update wishlist.'),
            ];
        }

        Wishlist::invalidateCurrentUserCache();

        return [
            'success'     => true,
            'in_wishlist' => true,
            'message'     => T::tr('Added to wishlist.'),
        ];
    }

    public function actionRemove(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $setId = (int)Yii::$app->request->post('set_id', 0);
        if ($setId <= 0) {
            throw new BadRequestHttpException('Invalid set.');
        }

        $userId = (int)Yii::$app->user->id;
        $entry = Wishlist::findOne(['user_id' => $userId, 'set_id' => $setId]);
        if ($entry) {
            $entry->delete();
            Wishlist::invalidateCurrentUserCache();
        }

        return [
            'success'     => true,
            'in_wishlist' => false,
            'message'     => T::tr('Removed from wishlist.'),
        ];
    }
}
