<?php

namespace backend\modules\admin\controllers;

use backend\modules\admin\models\ThemeSearch;
use common\enums\StatusEnum;
use common\models\Theme;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ThemeController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'toggle-status' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new ThemeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'groupsList' => Theme::getAvailableGroupsList(),
            'parentsList' => Theme::getAvailableParentThemesList(),
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
        $model = new Theme();
        $model->status = StatusEnum::ACTIVE->value;

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'groupsList' => Theme::getAvailableGroupsList(),
            'parentsList' => Theme::getAvailableParentThemesList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'groupsList' => Theme::getAvailableGroupsList(),
            'parentsList' => Theme::getAvailableParentThemesList($model->id),
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
        $model->status = $isActive ? StatusEnum::ACTIVE->value : StatusEnum::INACTIVE->value;
        $model->save(false, ['status']);

        return $this->redirect($this->request->post('returnUrl', ['index']));
    }

    protected function findModel($id): Theme
    {
        if (($model = Theme::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
