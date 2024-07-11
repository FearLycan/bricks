<?php

namespace frontend\modules\product\controllers;

use common\components\Controller;

class ProductController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index', []);
    }

    public function actionView(string $slug): string
    {
        return $this->render('view', []);
    }
}