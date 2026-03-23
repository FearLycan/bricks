<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use backend\modules\admin\models\SetOfferImportSearch;
use common\models\SetOfferImport;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class SetOfferImportController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new SetOfferImportSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel((int)$id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel((int)$id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel(int $id): SetOfferImport
    {
        $model = SetOfferImport::findOne(['id' => $id]);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
