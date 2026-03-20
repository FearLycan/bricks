<?php

use frontend\components\LinkPager;
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
    'itemOptions'  => ['class' => 'col-6 col-lg-3 col-md-4 lego-set mb-4'],
    'itemView'     => '_item',
    'options'      => ['class' => 'row'],
    'summary'      => false,
    'pager'        => [
        'class' => LinkPager::class,
    ],
]) ?>
