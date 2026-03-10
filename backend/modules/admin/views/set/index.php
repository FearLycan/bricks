<?php

use common\models\Set;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\modules\admin\models\SetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Sets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="set-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Set', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'number',
            'name',
            'slug',
            'theme_id',
            //'status',
            //'number_variant',
            //'minifigures',
            //'year',
            //'pieces',
            //'released',
            //'brickset_url:url',
            //'rating',
            //'price',
            //'age',
            //'dimensions',
            //'availability',
            //'created_at',
            //'updated_at',
            //'subtheme_id',
            //'description:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Set $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
