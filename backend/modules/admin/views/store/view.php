<?php

use common\models\Store;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var Store        $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-view">
    <div class="d-flex align-items-center gap-2 mb-4">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Back to list', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'data'  => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method'  => 'post',
                    ],
            ]) ?>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="badge rounded-pill <?= $model->isActive() ? 'text-bg-success' : 'text-bg-danger' ?>">Status: <?= Html::encode($model->getStatusLabel()) ?></span>
    </div>

    <?= DetailView::widget([
            'model'      => $model,
            'options'    => ['class' => 'table table-sm detail-view backend-detail-view'],
            'attributes' => [
                    'id',
                    'code',
                    'name',
                    'url:url',
                    'logo',
                    [
                            'attribute' => 'status',
                            'value'     => $model->getStatusLabel(),
                    ],
                    'created_at',
                    'updated_at',
            ],
    ]) ?>
</div>
