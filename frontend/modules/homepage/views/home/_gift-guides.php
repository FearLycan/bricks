<?php

use frontend\components\GiftGuide;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Homepage strip linking to the gift guides. One guide = one full-width
 * strip; more guides sit side by side (grid auto-fits).
 *
 * @var View $this
 */

$guides = GiftGuide::all();
if ($guides === []) {
    return;
}
?>

<section class="bricks-section bricks-section--gift-guides" id="gift-ideas">
    <div class="container">
        <div class="bricks-gift-guides">
            <?php foreach ($guides as $guide): ?>
                <a class="bricks-gift-guide-strip"
                   href="<?= Html::encode(Url::to(['/lego/lego/gift-guide', 'slug' => $guide['slug']])) ?>">
                    <span class="bricks-gift-guide-strip-content">
                        <span class="bricks-gift-guide-strip-eyebrow"><?= Html::encode(T::tr('Gift guide')) ?></span>
                        <span class="bricks-gift-guide-strip-title"><?= Html::encode($guide['title']) ?></span>
                        <span class="bricks-gift-guide-strip-lead"><?= Html::encode($guide['teaser']) ?></span>
                    </span>
                    <span class="bricks-gift-guide-strip-action">
                        <?= Html::encode(T::tr('See the guide')) ?>
                        <i class="ph ph-arrow-right" aria-hidden="true"></i>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
