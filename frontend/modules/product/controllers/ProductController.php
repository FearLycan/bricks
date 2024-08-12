<?php

namespace frontend\modules\product\controllers;

use common\components\AccessControl;
use common\components\Controller;

class ProductController extends Controller
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

    public function actionIndex(): string
    {
        return $this->render('index', []);
    }

    public function actionView(string $slug): string
    {
        return $this->render('view', []);
    }
}