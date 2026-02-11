<?php

use common\components\Html;
use frontend\models\searches\SetSearch;
use yii\bootstrap5\LinkPager;
use yii\data\ActiveDataProvider;
use yii\web\View;
use yii\widgets\ListView;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 * @var $searchModel  SetSearch
 */

$this->title = Html::encode(Yii::$app->name);
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <?= $this->title ?>
    </h1>
</div>

<?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions'  => ['class' => 'col-lg-3 col-md-3 lego-set mb-4'],
        'itemView'     => '_item',
        'options'      => ['class' => 'row'],
        'summary'      => false,
        'pager'        => [
                'class'   => LinkPager::class,
                'options' => ['class' => 'pagination justify-content-center mt-4'],
        ],
]) ?>

