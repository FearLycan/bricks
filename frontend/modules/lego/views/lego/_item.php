<?php

use common\models\Set;
use frontend\components\T;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $this  View
 * @var $model Set
 */

?>

<a href="<?= "/lego/{$model->slug}" ?>" class="text-decoration-none text-reset">
    <div class="card h-100">
        <div class="set-card-img-wrap">
            <img src="<?= $model->getMainImage()->url ?? "https://placehold.co/300x220?text={$model->number}" ?>"
                 class="card-img-top img-fluid set-card-image"
                 loading="lazy"
                 alt="<?= Html::encode($model->name) ?>">
            <?php $savingsPercent = $model->getPromotionalSavingsPercent('USD'); ?>
            <?php if ($savingsPercent !== null): ?>
                <span class="set-card-discount-badge">−<?= $savingsPercent ?>%</span>
            <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column pb-2">
            <p class="set-card-number text-muted mb-1"><?= Html::encode($model->number) ?></p>
            <h5 class="card-title set-card-title flex-grow-1">
                <?= Html::encode($model->name) ?>
            </h5>
            <?php $promoPrice = $model->getFormattedPromotionalPrice('USD'); ?>
            <?php if ($promoPrice !== null): ?>
                <div class="mt-2">
                    <span class="fw-bold text-success font-size-large"><?= Html::encode($promoPrice) ?></span>
                    <span class="text-muted text-decoration-line-through ms-2 small"><?= Html::encode($model->getFormattedPrice('USD') ?? '') ?></span>
                </div>
            <?php elseif ($model->getFormattedPrice('USD') !== null): ?>
                <div class="mt-2">
                    <span class="fw-bold"><?= Html::encode($model->getFormattedPrice('USD')) ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($model->pieces !== null || $model->year !== null || $model->age !== null): ?>
            <div class="set-card-meta">
                <?php if ($model->pieces !== null): ?>
                    <span class="set-card-meta-item" title="<?= T::tr('Pieces') ?>">
                        <i class="bi bi-columns-gap me-1"></i>
                        <?= Html::encode($model->pieces) ?> pcs
                    </span>
                <?php endif; ?>
                <?php if ($model->year !== null): ?>
                    <span class="set-card-meta-item" title="<?= T::tr('Release year') ?>">
                        <i class="bi bi-calendar-check me-1"></i>
                        <?= Html::encode($model->year) ?>
                    </span>
                <?php endif; ?>
                <?php if ($model->age !== null): ?>
                    <span class="set-card-meta-item" title="<?= T::tr("Age restriction") ?>">
                        <i class="bi bi-cake me-1"></i>
                        <?= Html::encode($model->age) ?>+
                    </span>
                <?php endif; ?>

                <?php if($model->minifigures): ?>
                    <span class="set-card-meta-item" title="<?= T::tr("Minifigures") ?>">
                        <i class="bi bi-people me-1"></i>
                        <?= Html::encode($model->minifigures) ?>
                    </span>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</a>
