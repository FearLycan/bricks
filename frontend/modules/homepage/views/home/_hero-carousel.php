<?php

use common\models\Set;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View  $this
 * @var Set[] $slides
 */

if (empty($slides)) {
    return;
}

$this->registerCssFile('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [
    'position' => View::POS_HEAD,
]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [
    'position' => View::POS_END,
]);

$heroId = 'bricks-hero-' . uniqid();
?>

<section class="bricks-hero-carousel">
    <div class="swiper bricks-hero-swiper" id="<?= Html::encode($heroId) ?>">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $slide):
                $imageUrl  = $slide->getDisplayMainImageUrl();
                $themeName = $slide->theme ? $slide->theme->name : '';
                $promoPrice = $slide->getFormattedPromotionalPrice('USD');
                $basePrice  = $slide->getFormattedPrice('USD');
                $savings    = $slide->getPromotionalSavingsPercent('USD');
            ?>
                <div class="swiper-slide bricks-hero-slide">
                    <a href="<?= Url::to("/lego/{$slide->slug}") ?>" class="bricks-hero-slide-link">
                        <div class="bricks-hero-slide-media">
                            <div class="bricks-hero-slide-frame">
                                <img src="<?= Html::encode($imageUrl) ?>"
                                     alt="<?= Html::encode((string)$slide->name) ?>"
                                     loading="eager"
                                     class="bricks-hero-slide-image">
                            </div>
                        </div>
                        <div class="bricks-hero-slide-content">
                            <?php if ($themeName !== ''): ?>
                                <span class="bricks-hero-slide-eyebrow"><?= Html::encode($themeName) ?></span>
                            <?php endif; ?>
                            <h2 class="bricks-hero-slide-title"><?= Html::encode((string)$slide->name) ?></h2>
                            <p class="bricks-hero-slide-meta">
                                <span><?= Html::encode((string)$slide->number) ?></span>
                                <?php if ($slide->pieces): ?>
                                    <span class="dot-sep">&middot;</span>
                                    <span><?= T::tr('{n} pieces', ['n' => (int)$slide->pieces]) ?></span>
                                <?php endif; ?>
                                <?php if ($slide->rating): ?>
                                    <span class="dot-sep">&middot;</span>
                                    <span class="bricks-hero-slide-rating">
                                        <i class="bi bi-star-fill"></i>
                                        <?= Html::encode(number_format((float)$slide->rating, 1)) ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                            <?php if ($promoPrice !== null): ?>
                                <p class="bricks-hero-slide-price">
                                    <span class="bricks-hero-slide-price-now"><?= Html::encode($promoPrice) ?></span>
                                    <?php if ($basePrice !== null): ?>
                                        <span class="bricks-hero-slide-price-was"><?= Html::encode($basePrice) ?></span>
                                    <?php endif; ?>
                                    <?php if ($savings !== null): ?>
                                        <span class="bricks-hero-slide-price-badge">−<?= (int)$savings ?>%</span>
                                    <?php endif; ?>
                                </p>
                            <?php elseif ($basePrice !== null): ?>
                                <p class="bricks-hero-slide-price">
                                    <span class="bricks-hero-slide-price-now"><?= Html::encode($basePrice) ?></span>
                                </p>
                            <?php endif; ?>
                            <span class="bricks-hero-slide-cta">
                                <?= Html::encode(T::tr('Compare prices')) ?>
                                <i class="bi bi-arrow-right ms-1"></i>
                            </span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
        <button type="button" class="swiper-button-prev" aria-label="<?= Html::encode(T::tr('Previous slide')) ?>"></button>
        <button type="button" class="swiper-button-next" aria-label="<?= Html::encode(T::tr('Next slide')) ?>"></button>
    </div>
</section>

<?php
$js = <<<JS
(function () {
    function init() {
        if (typeof window.Swiper !== 'function') {
            setTimeout(init, 80);
            return;
        }
        var el = document.getElementById('{$heroId}');
        if (!el || el.dataset.bricksHeroInit === '1') {
            return;
        }
        el.dataset.bricksHeroInit = '1';
        new window.Swiper(el, {
            loop: true,
            speed: 600,
            autoplay: { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
            navigation: {
                nextEl: el.querySelector('.swiper-button-next'),
                prevEl: el.querySelector('.swiper-button-prev'),
            },
            keyboard: { enabled: true },
            grabCursor: true,
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
JS;
$this->registerJs($js, View::POS_END);
