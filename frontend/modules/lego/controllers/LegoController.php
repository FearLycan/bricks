<?php

namespace frontend\modules\lego\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\Set;
use frontend\models\searches\SetSearch;

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
                            'index', 'view',
                        ],
                        'roles'   => ['?'],
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

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    private function findModel(string $slug): Set
    {
        $model = Set::find()->where(['slug' => $slug])->one();

        if (!$model) {
            $this->notFound();
        }

        return $model;
    }
}