<?php

use common\enums\image\StatusEnum;
use common\models\Set;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Set $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$statusLabel = StatusEnum::tryFrom((int)$model->status)?->label() ?? '-';
?>
<div class="set-view">
    <div class="d-flex align-items-center gap-2 mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary">Set #<?= Html::encode($model->number ?: '-') ?></div>
        </div>
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
        <span class="badge rounded-pill text-bg-light border text-dark">Theme: <?= Html::encode($model->theme->name ?? '-') ?></span>
        <span class="badge rounded-pill text-bg-light border text-dark">Subtheme: <?= Html::encode($model->subtheme->name ?? '-') ?></span>
        <span class="badge rounded-pill text-bg-light border text-dark">Status: <?= Html::encode($statusLabel) ?></span>
        <span class="badge rounded-pill text-bg-light border text-dark">Year: <?= Html::encode((string)($model->year ?? '-')) ?></span>
        <span class="badge rounded-pill text-bg-light border text-dark">Pieces: <?= Html::encode((string)($model->pieces ?? '-')) ?></span>
    </div>

    <div class="row g-4">
        <?php if ($model->images): ?>
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Images</h2>
                        <div class="backend-image-preview-main mb-3">
                            <?= Html::a(Html::img($model->getDisplayMainImageUrl(), [
                                    'alt'     => Html::encode($model->name),
                                    'class'   => 'img-fluid rounded',
                                    'loading' => 'lazy',
                            ]), $model->getDisplayMainImageUrl(), ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($model->images as $image): ?>
                                <?= Html::a(Html::img($image->url, [
                                        'alt'     => Html::encode($model->name),
                                        'class'   => 'backend-image-thumb rounded border',
                                        'loading' => 'lazy',
                                ]), $image->url, ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Basic information</h2>
                    <?= DetailView::widget([
                            'model'      => $model,
                            'options'    => ['class' => 'table table-sm detail-view backend-detail-view mb-0'],
                            'attributes' => [
                                    'id',
                                    'number',
                                    'number_variant',
                                    'name',
                                    'slug',
                                    [
                                            'label' => 'Theme',
                                            'value' => $model->theme->name ?? '-',
                                    ],
                                    [
                                            'label' => 'Subtheme',
                                            'value' => $model->subtheme->name ?? '-',
                                    ],
                                    [
                                            'attribute' => 'status',
                                            'value'     => $statusLabel,
                                    ],
                                    [
                                            'attribute' => 'released',
                                            'value'     => $model->released === null ? '-' : ($model->released ? 'Yes' : 'No'),
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Set details</h2>
                    <?= DetailView::widget([
                            'model'      => $model,
                            'options'    => ['class' => 'table table-sm detail-view backend-detail-view mb-0'],
                            'attributes' => [
                                    'year',
                                    'pieces',
                                    'minifigures',
                                    'age',
                                    'rating',
                                    'price',
                                    'availability',
                                    'dimensions',
                                    'brickset_url:url',
                            ],
                    ]) ?>
                </div>
            </div>
        </div>

        <?php if ($model->description): ?>
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Description</h2>
                        <div class="backend-description">
                            <?= nl2br(Html::encode($model->description)) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 mb-3">Timestamps</h2>
                    <?= DetailView::widget([
                            'model'      => $model,
                            'options'    => ['class' => 'table table-sm detail-view backend-detail-view mb-0'],
                            'attributes' => [
                                    'created_at',
                                    'updated_at',
                            ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

</div>
