<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Set $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="set-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'number',
            'name',
            'slug',
            'theme_id',
            'status',
            'number_variant',
            'minifigures',
            'year',
            'pieces',
            'released',
            'brickset_url:url',
            'rating',
            'price',
            'age',
            'dimensions',
            'availability',
            'created_at',
            'updated_at',
            'subtheme_id',
            'description:ntext',
        ],
    ]) ?>

</div>
