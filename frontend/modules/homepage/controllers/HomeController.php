<?php

namespace frontend\modules\homepage\controllers;

use common\components\AccessControl;

use common\components\Controller;
use frontend\models\searches\SetSearch;
use Yii;
use yii\caching\Cache;

class HomeController extends Controller
{
    //private Cache $cache;

    public function __construct($id, $module, $config = [])
    {
        //$this->cache = Yii::$app->cache;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => [
                            'index',
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
}