<?php

use common\models\OwnedSet;
use common\models\Set;
use common\models\Wishlist;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var Set  $model
 */

$isGuest = Yii::$app->user->isGuest;
$isWishlisted = !$isGuest && Wishlist::isInCurrentUserWishlist((int)$model->id);
$isOwned = !$isGuest && OwnedSet::isOwnedByCurrentUser((int)$model->id);

$mainImage = $model->getMainImage();
$imageUrl = $mainImage !== null ? $mainImage->url : "https://placehold.co/300x220?text={$model->number}";

$promoPrice = $model->getFormattedPromotionalPrice('USD');
$basePrice = $model->getFormattedPrice('USD');
$savingsPercent = $model->getPromotionalSavingsPercent('USD');

$availability = (string)$model->availability;
$showAvailabilityBadge = $availability !== '' && $availability !== '{Not specified}';
$availBadgeClass = '';
if ($showAvailabilityBadge) {
    $availLower = strtolower($availability);
    if (str_contains($availLower, 'exclusive')) {
        $availBadgeClass = 'set-card-availability-badge--exclusive';
    } elseif (str_contains($availLower, 'available') || str_contains($availLower, 'retail')) {
        $availBadgeClass = 'set-card-availability-badge--available';
    } else {
        $availBadgeClass = 'set-card-availability-badge--unavailable';
    }
}
?>

<a href="<?= Url::to("/lego/{$model->slug}") ?>" class="text-decoration-none text-reset bricks-set-card">
    <div class="card h-100">
        <div class="set-card-img-wrap">
            <?php if (!$isGuest && $model->isReleased()): ?>
                <button type="button"
                        class="js-owned-set-toggle set-card-owned-btn <?= $isOwned ? 'is-active' : '' ?>"
                        data-set-id="<?= (int)$model->id ?>"
                        data-toggle-url="<?= Html::encode(Url::to(['/owned-set/toggle'])) ?>"
                        aria-pressed="<?= $isOwned ? 'true' : 'false' ?>"
                        aria-label="<?= Html::encode(T::tr('Toggle owned set')) ?>"
                        title="<?= Html::encode($isOwned ? T::tr('Remove from owned sets') : T::tr('Add to owned sets')) ?>">
                    <i class="bi <?= $isOwned ? 'bi-box-seam-fill' : 'bi-box-seam' ?>"></i>
                </button>
            <?php endif; ?>
            <?php if (!$isGuest): ?>
                <button type="button"
                        class="js-wishlist-toggle set-card-wishlist-btn <?= $isWishlisted ? 'is-active' : '' ?>"
                        data-set-id="<?= (int)$model->id ?>"
                        data-toggle-url="<?= Html::encode(Url::to(['/wishlist/toggle'])) ?>"
                        aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
                        aria-label="<?= Html::encode(T::tr('Toggle wishlist')) ?>"
                        title="<?= Html::encode($isWishlisted ? T::tr('Remove from wishlist') : T::tr('Add to wishlist')) ?>">
                    <i class="bi <?= $isWishlisted ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                </button>
            <?php endif; ?>
            <img src="<?= Html::encode($imageUrl) ?>"
                 class="card-img-top img-fluid set-card-image"
                 loading="lazy"
                 alt="<?= Html::encode($model->name) ?>">
            <?php if ($savingsPercent !== null): ?>
                <span class="set-card-discount-badge">−<?= $savingsPercent ?>%</span>
            <?php endif; ?>
            <?php if ($showAvailabilityBadge): ?>
                <span class="set-card-availability-badge <?= $availBadgeClass ?>">
                    <?= Html::encode($availability) ?>
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
                <?php
                $ratingFull = (int)floor((float)$model->rating);
                $ratingHalf = ((float)$model->rating - $ratingFull) >= 0.5;
                ?>
                <div class="set-card-rating">
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
            <?php if ($promoPrice !== null): ?>
                <div class="mt-2">
                    <span class="fw-bold text-success font-size-large"><?= Html::encode($promoPrice) ?></span>
                    <span class="text-muted text-decoration-line-through ms-2 small"><?= Html::encode($basePrice ?? '') ?></span>
                </div>
            <?php elseif ($basePrice !== null): ?>
                <div class="mt-2">
                    <span class="fw-bold"><?= Html::encode($basePrice) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</a>
