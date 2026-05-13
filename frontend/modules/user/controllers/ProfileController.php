<?php

namespace frontend\modules\user\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\OwnedSet;
use common\models\User;
use common\models\Wishlist;
use Yii;

class ProfileController extends Controller
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
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $ownedCount = OwnedSet::find()->where(['user_id' => (int)$user->id])->count();
        $wishlistCount = Wishlist::find()->where(['user_id' => (int)$user->id])->count();

        return $this->render('index', [
            'user'          => $user,
            'ownedCount'    => (int)$ownedCount,
            'wishlistCount' => (int)$wishlistCount,
        ]);
    }
}
