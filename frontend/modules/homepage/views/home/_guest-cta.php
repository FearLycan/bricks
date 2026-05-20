<?php

use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 */

if (!Yii::$app->user->isGuest) {
    return;
}
?>

<section class="bricks-section bricks-section--guest-cta">
    <div class="container">
        <div class="bricks-guest-cta">
            <div class="bricks-guest-cta-content">
                <span class="bricks-guest-cta-eyebrow"><?= Html::encode(T::tr('Join BrickAtlas')) ?></span>
                <h2 class="bricks-guest-cta-title">
                    <?= T::tr('Track every LEGO{sup} set you love', ['sup' => '<sup>®</sup>']) ?>
                </h2>
                <p class="bricks-guest-cta-lead">
                    <?= Html::encode(T::tr('Create a free account to save sets to your wishlist, mark what you own, and get personalized price drops.')) ?>
                </p>
                <ul class="bricks-guest-cta-benefits">
                    <li><i class="bi bi-heart-fill"></i><?= Html::encode(T::tr('Save sets to your wishlist')) ?></li>
                    <li><i class="bi bi-box-seam-fill"></i><?= Html::encode(T::tr('Track sets you already own')) ?></li>
                    <li><i class="bi bi-stars"></i><?= Html::encode(T::tr('Get recommendations based on your collection')) ?></li>
                </ul>
                <div class="bricks-guest-cta-actions">
                    <a href="<?= Url::to(['/auth/signup']) ?>" class="bricks-guest-cta-primary">
                        <?= Html::encode(T::tr('Create free account')) ?>
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="<?= Url::to(['/auth/login']) ?>" class="bricks-guest-cta-secondary">
                        <?= Html::encode(T::tr('Sign in')) ?>
                    </a>
                </div>
            </div>
            <div class="bricks-guest-cta-decoration" aria-hidden="true">
                <i class="bi bi-bricks"></i>
            </div>
        </div>
    </div>
</section>
