<?php

namespace frontend\modules\user\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\User;
use frontend\components\T;
use frontend\modules\user\models\SettingsForm;
use Yii;

class SettingsController extends Controller
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

    public function actionIndex()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $form = new SettingsForm($user);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash('success', T::tr('Settings saved.'));

            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $form,
            'user'  => $user,
        ]);
    }
}
