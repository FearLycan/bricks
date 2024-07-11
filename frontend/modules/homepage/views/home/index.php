<?php

use common\components\Html;
use yii\web\View;

/**
 * @var $this View
 */

$this->title = Html::encode(Yii::$app->name);
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <?= $this->title ?>
    </h1>
</div>

