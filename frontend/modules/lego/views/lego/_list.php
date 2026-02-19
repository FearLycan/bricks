<?php

use yii\bootstrap5\LinkPager;
use yii\data\ActiveDataProvider;
use yii\web\View;
use yii\widgets\ListView;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 */

?>


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
