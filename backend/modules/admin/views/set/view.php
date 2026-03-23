<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\enums\SetOfferImportStatusEnum;
use common\models\SetOfferImport;

/**
 * @var yii\web\View      $this
 * @var common\models\Set $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="set-view">
    <div class="d-flex align-items-center gap-2 mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary">Set #<?= Html::encode($model->number ?: '-') ?></div>
        </div>
        <div class="ms-auto d-flex gap-2">
            <?php if ($model->slug): ?>
                <?= Html::a('View on frontend', Yii::$app->frontendUrlManager->createAbsoluteUrl(["/lego/{$model->slug}"]), [
                        'class'  => 'btn btn-sm btn-outline-info',
                        'target' => '_blank',
                        'rel'    => 'noopener noreferrer',
                ]) ?>
            <?php endif; ?>
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
        <span class="badge rounded-pill <?= $model->isActive() ? 'text-bg-success' : 'text-bg-danger' ?>">Status: <?= Html::encode($model->getStatusLabel()) ?></span>
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
                                            'value'     => $model->getStatusLabel(),
                                    ],
                                    [
                                            'attribute' => 'released',
                                            'value'     => $model->getReleasedLabel(),
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

        <div class="col-12" id="offers">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <h2 class="h5 mb-0">Offers</h2>
                        <div class="ms-auto">
                            <div class="d-flex gap-2">
                                <?= Html::a('Import AliExpress', ['/admin/set-offer/import-aliexpress', 'setId' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::a('Add Offer', ['/admin/set-offer/create', 'setId' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($model->setOffers !== []): ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Store</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Availability</th>
                                    <th>Source</th>
                                    <th>Manual</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model->setOffers as $offer): ?>
                                    <tr>
                                        <td><?= Html::encode($offer->store->name ?? '-') ?></td>
                                        <td><?= Html::encode($offer->getDisplayNameOrDefault()) ?></td>
                                        <td><?= Html::encode($offer->getFormattedPriceOrDefault()) ?></td>
                                        <td><?= Html::encode($offer->availability ?: '-') ?></td>
                                        <td><?= Html::encode($offer->source ?: '-') ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= (int)$offer->is_manual_override === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                                <?= (int)$offer->is_manual_override === 1 ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <?= Html::a('<i class="bi bi-pencil"></i>', ['/admin/set-offer/update', 'id' => $offer->id], [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'title' => 'Update offer',
                                                ]) ?>
                                                <?= Html::a('<i class="bi bi-trash"></i>', ['/admin/set-offer/delete', 'id' => $offer->id], [
                                                        'class'        => 'btn btn-sm btn-outline-danger',
                                                        'title'        => 'Delete offer',
                                                        'data-method'  => 'post',
                                                        'data-confirm' => 'Are you sure you want to delete this offer?',
                                                ]) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-body-secondary">No offers added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 mb-3">AliExpress import queue</h2>
                    <?php if ($model->setOfferImports !== []): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Status</th>
                                    <th>Attempts</th>
                                    <th>Error</th>
                                    <th>Updated</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model->setOfferImports as $import): ?>
                                    <tr>
                                        <td class="text-break"><?= Html::encode($import->input_url) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = match ($import->status) {
                                                SetOfferImportStatusEnum::DONE->value => 'text-bg-success',
                                                SetOfferImportStatusEnum::FAILED->value => 'text-bg-danger',
                                                SetOfferImportStatusEnum::PROCESSING->value => 'text-bg-primary',
                                                default => 'text-bg-secondary',
                                            };
                                            ?>
                                            <span class="badge rounded-pill <?= $statusClass ?>">
                                                <?= Html::encode($import->getStatusLabel()) ?>
                                            </span>
                                        </td>
                                        <td><?= Html::encode((string)$import->attempts) ?></td>
                                        <td><?= Html::encode($import->error_message ?: '-') ?></td>
                                        <td><?= Html::encode((string)($import->updated_at ?? $import->created_at)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-body-secondary mb-0">No queued imports yet.</p>
                    <?php endif; ?>
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
