<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use backend\modules\admin\models\UserSearch;
use common\enums\UserRoleEnum;
use common\models\User;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class'   => VerbFilter::className(),
                    'actions' => [
                        'delete'        => ['POST'],
                        'toggle-status' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->role = UserRoleEnum::USER->value;
        $model->status = User::STATUS_ACTIVE;
        $password = '';

        if ($this->request->isPost) {
            $password = (string)$this->request->post('new_password', '');
            if ($model->load($this->request->post())) {
                if ($password === '') {
                    $model->addError('password_hash', 'Password cannot be blank.');
                } else {
                    $model->setPassword($password);
                    $model->generateAuthKey();
                    $model->generateEmailVerificationToken();

                    if ($model->save()) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        return $this->render('create', [
            'model'    => $model,
            'password' => $password,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $password = '';

        if ($this->request->isPost) {
            $password = (string)$this->request->post('new_password', '');
            if ($model->load($this->request->post())) {
                if ($password !== '') {
                    $model->setPassword($password);
                }

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model'    => $model,
            'password' => $password,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        $isActive = (bool)$this->request->post('status', false);
        $model->status = $isActive ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;
        $model->save(false, ['status']);

        return $this->redirect($this->request->post('returnUrl', ['index']));
    }

    protected function findModel($id): User
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
