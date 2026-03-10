<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;

/**
 * Default controller for the `admin` module
 */
class DashboardController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
