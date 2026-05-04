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
            <?php if ($model->availability !== null && $model->availability !== '' && $model->availability !== '{Not specified}'): ?>
                <?php
                $availLower = strtolower($model->availability);
                if (str_contains($availLower, 'exclusive')) {
                    $availBadgeClass = 'set-card-availability-badge--exclusive';
                } elseif (str_contains($availLower, 'available') || str_contains($availLower, 'retail')) {
                    $availBadgeClass = 'set-card-availability-badge--available';
                } else {
                    $availBadgeClass = 'set-card-availability-badge--unavailable';
                }
                ?>
                <span class="set-card-availability-badge <?= $availBadgeClass ?>">
                    <?= Html::encode($model->availability) ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column pb-2">
            <p class="set-card-number text-muted mb-1"><?= Html::encode($model->number) ?></p>
            <?php if ($model->subtheme): ?>
                <span class="set-card-theme-tag mb-2"><?= Html::encode($model->subtheme->name) ?></span>
            <?php endif; ?>
            <h5 class="card-title set-card-title flex-grow-1">
                <?= Html::encode($model->name) ?>
            </h5>
            <?php if ($model->rating): ?>
                <div class="set-card-rating">
                    <?php $ratingFull = floor((float)$model->rating); ?>
                    <?php $ratingHalf = ((float)$model->rating - $ratingFull) >= 0.5; ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= $ratingFull): ?>
                            <i class="bi bi-star-fill"></i>
                        <?php elseif ($ratingHalf && $i === $ratingFull + 1): ?>
                            <i class="bi bi-star-half"></i>
                        <?php else: ?>
                            <i class="bi bi-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <span class="set-card-rating-value"><?= Html::encode(number_format((float)$model->rating, 1)) ?></span>
                </div>
            <?php endif; ?>
            <?php $promoPriceCents = $model->getPromotionalPriceCents('USD'); ?>
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
            <?php
            $effectivePriceCents = $promoPriceCents ?? $model->price;
            if ($effectivePriceCents !== null && $effectivePriceCents > 0 && $model->pieces > 0):
                $ppp = $effectivePriceCents / 100 / $model->pieces;
            ?>
                <div class="set-card-ppp text-muted">
                    <?= number_format($ppp, 3) ?> USD/pcs
                </div>
            <?php endif; ?>
        </div>
        <?php if ($model->age !== null || $model->pieces !== null || $model->year !== null || $model->minifigures || $model->rating): ?>
            <div class="set-card-meta">
                <?php if ($model->age !== null): ?>
                    <span class="set-card-meta-item" title="<?= T::tr('Age restriction') ?>">
                        <i class="bi bi-cake me-1"></i>
                        <?= Html::encode($model->age) ?>+
                    </span>
                <?php endif; ?>
                <?php if ($model->pieces !== null): ?>
                    <span class="set-card-meta-item" title="<?= T::tr('Pieces') ?>">
                        <i class="bi bi-columns-gap me-1"></i>
                        <?= Html::encode($model->pieces) ?>
                    </span>
                <?php endif; ?>
                <?php if ($model->year !== null): ?>
                    <span class="set-card-meta-item" title="<?= T::tr('Release year') ?>">
                        <i class="bi bi-calendar-check me-1"></i>
                        <?= Html::encode($model->year) ?>
                    </span>
                <?php endif; ?>
                <?php if ($model->minifigures): ?>
                    <span class="set-card-meta-item" title="<?= T::tr("Minifigures") ?>">
                        <i class="bi bi-people me-1"></i>
                        <?= Html::encode($model->minifigures) ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</a>
