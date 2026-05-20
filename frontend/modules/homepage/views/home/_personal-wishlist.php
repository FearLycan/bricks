<?php

use common\models\Set;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View  $this
 * @var Set[] $items
 */

if (empty($items)) {
    return;
}
?>

<section class="bricks-section bricks-section--wishlist-bar">
    <div class="container">
        <div class="bricks-wishlist-bar">
            <div class="bricks-wishlist-bar-head">
                <div class="bricks-wishlist-bar-title-wrap">
                    <span class="bricks-wishlist-bar-icon"><i class="bi bi-heart-fill"></i></span>
                    <div>
                        <h2 class="bricks-section-title mb-0"><?= Html::encode(T::tr('Your wishlist')) ?></h2>
                        <p class="bricks-wishlist-bar-sub mb-0">
                            <?= Html::encode(T::tr('Latest items you saved for later.')) ?>
                        </p>
                    </div>
                </div>
                <a href="<?= Url::to(['/wishlist/index']) ?>" class="bricks-section-see-all">
                    <?= Html::encode(T::tr('See all')) ?>
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="bricks-wishlist-bar-items">
                <?php foreach ($items as $set):
                    $mainImage = $set->getMainImage();
                    $imageUrl  = $mainImage !== null ? $mainImage->url : "https://placehold.co/120x120?text={$set->number}";
                ?>
                    <a href="<?= Url::to("/lego/{$set->slug}") ?>" class="bricks-wishlist-bar-item">
                        <span class="bricks-wishlist-bar-item-media">
                            <img src="<?= Html::encode($imageUrl) ?>"
                                 alt="<?= Html::encode((string)$set->name) ?>"
                                 loading="lazy">
                        </span>
                        <span class="bricks-wishlist-bar-item-body">
                            <span class="bricks-wishlist-bar-item-number"><?= Html::encode((string)$set->number) ?></span>
                            <span class="bricks-wishlist-bar-item-name"><?= Html::encode((string)$set->name) ?></span>
                            <?php $price = $set->getFormattedPromotionalPrice('USD') ?? $set->getFormattedPrice('USD'); ?>
                            <?php if ($price !== null): ?>
                                <span class="bricks-wishlist-bar-item-price"><?= Html::encode($price) ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
