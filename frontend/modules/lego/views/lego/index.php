<?php

use common\components\Html;
use frontend\models\searches\SetSearch;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 * @var $searchModel  SetSearch
 */

$this->title = Html::encode(Yii::$app->name);

?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <?= $this->title ?>
    </h1>
</div>

<?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>



