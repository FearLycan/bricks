<?php

use common\models\Theme;
use frontend\components\T;
use yii\web\View;

/**
 * @var View       $this
 * @var array      $heroSlides
 * @var array      $themeTiles
 * @var array      $browseTabs
 * @var array      $newArrivals
 * @var array      $onSale
 * @var array      $topRated
 * @var array      $comingSoon
 * @var array      $forAdults
 * @var Theme|null $themeSpotlight
 * @var array      $featuredMinifigs
 * @var array      $wishlistPreview
 * @var array      $recommendations
 * @var array|null $collectionStats
 */

$this->title = Yii::$app->name;
$this->params['breadcrumbs'] = [];
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/homepage.css', ['depends' => [\frontend\assets\AppAsset::class]]);

$isGuest = Yii::$app->user->isGuest;
?>

<div class="bricks-homepage">

    <?php if (!empty($heroSlides)): ?>
        <?= $this->render('_hero-carousel', ['slides' => $heroSlides]) ?>
    <?php else: ?>
        <section class="bricks-hero bricks-hero--placeholder">
            <div class="container">
                <div class="bricks-hero-content">
                    <p class="bricks-hero-eyebrow"><?= htmlspecialchars(Yii::$app->name, ENT_QUOTES, 'UTF-8') ?></p>
                    <h1 class="bricks-hero-title">
                        <?= T::tr('Find the best deals on LEGO{sup} sets', ['sup' => '<sup>®</sup>']) ?>
                    </h1>
                    <p class="bricks-hero-lead">
                        <?= htmlspecialchars(T::tr('A homepage built around your collection — themes, new arrivals, price drops and recommendations, refreshed automatically as the catalogue updates.'), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?= $this->render('_wizard-cta') ?>

    <?php if (!$isGuest): ?>
        <?= $this->render('_personal-wishlist', ['items' => $wishlistPreview]) ?>
    <?php endif; ?>

    <?= $this->render('_browse-tabs', ['tabs' => $browseTabs]) ?>

    <?= $this->render('_theme-tiles', [
            'themes' => $themeTiles,
            'title'  => T::tr('Shop by theme'),
    ]) ?>

    <?= $this->render('_section-slider', [
            'title'       => T::tr('New arrivals'),
            'items'       => $newArrivals,
            'seeAllUrl'   => ['/lego/new'],
            'seeAllLabel' => T::tr('See all'),
    ]) ?>

    <?= $this->render('_section-slider', [
            'title'         => T::tr('On sale'),
            'items'         => $onSale,
            'seeAllUrl'     => ['/lego/on-sale'],
            'seeAllLabel'   => T::tr('See all'),
            'modifierClass' => 'bricks-section--accent-sale',
    ]) ?>

    <?= $this->render('_theme-spotlight', ['theme' => $themeSpotlight]) ?>

    <?= $this->render('_section-slider', [
            'title' => T::tr('Top rated'),
            'items' => $topRated,
    ]) ?>

    <?php if (!$isGuest): ?>
        <?= $this->render('_personal-recommendations', ['items' => $recommendations]) ?>
    <?php endif; ?>

    <?= $this->render('_section-slider', [
            'title' => T::tr('Coming soon'),
            'items' => $comingSoon,
    ]) ?>

    <?= $this->render('_section-slider', [
            'title' => T::tr('LEGO{sup} for adults', ['sup' => '<sup>®</sup>']),
            'items' => $forAdults,
    ]) ?>

    <?= $this->render('_featured-minifigs', ['minifigs' => $featuredMinifigs]) ?>

    <?php if ($isGuest): ?>
        <?= $this->render('_guest-cta') ?>
    <?php else: ?>
        <?= $this->render('_personal-collection-stats', ['stats' => $collectionStats]) ?>
    <?php endif; ?>

</div>
