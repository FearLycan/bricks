<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\OwnedSet;
use common\models\Set;
use frontend\components\T;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class OwnedSetController extends Controller
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

        $query = OwnedSet::find()
            ->alias('o')
            ->innerJoinWith(['set s'], false)
            ->with(['set.mainImageRelation'])
            ->where(['o.user_id' => $userId])
            ->orderBy(['o.created_at' => SORT_DESC, 'o.id' => SORT_DESC]);

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
            throw new BadRequestHttpException(T::tr('Invalid set.'));
        }

        $set = Set::findOne($setId);
        if (!$set) {
            throw new BadRequestHttpException(T::tr('Set not found.'));
        }

        $userId = (int)Yii::$app->user->id;
        $existing = OwnedSet::findOne(['user_id' => $userId, 'set_id' => $setId]);

        if ($existing) {
            $existing->delete();
            OwnedSet::invalidateCurrentUserCache();

            return [
                'success'  => true,
                'is_owned' => false,
                'message'  => T::tr('Removed from owned sets.'),
            ];
        }

        if (!$set->isReleased()) {
            return [
                'success' => false,
                'message' => T::tr('This set has not been released yet.'),
            ];
        }

        $entry = new OwnedSet();
        $entry->user_id = $userId;
        $entry->set_id = $setId;
        if (!$entry->save()) {
            return [
                'success' => false,
                'message' => T::tr('Could not update owned sets.'),
            ];
        }

        OwnedSet::invalidateCurrentUserCache();

        return [
            'success'  => true,
            'is_owned' => true,
            'message'  => T::tr('Added to owned sets.'),
        ];
    }

    public function actionRemove(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $setId = (int)Yii::$app->request->post('set_id', 0);
        if ($setId <= 0) {
            throw new BadRequestHttpException(T::tr('Invalid set.'));
        }

        $userId = (int)Yii::$app->user->id;
        $entry = OwnedSet::findOne(['user_id' => $userId, 'set_id' => $setId]);
        if ($entry) {
            $entry->delete();
            OwnedSet::invalidateCurrentUserCache();
        }

        return [
            'success'  => true,
            'is_owned' => false,
            'message'  => T::tr('Removed from owned sets.'),
        ];
    }
}
