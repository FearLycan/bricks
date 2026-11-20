<?php

use frontend\components\GiftGuide;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * "Looking for a gift? Pick who it is for" box shown inside the homepage
 * Christmas banner during the gift season (GiftGuide::isGiftSeason()).
 *
 * @var View $this
 */
?>
<div class="bricks-gift-picker" id="gift-ideas">
    <div class="bricks-gift-picker-label"><?= Html::encode(T::tr('Looking for a gift? Pick who it is for')) ?></div>
    <div class="bricks-gift-picker-grid">
        <?php foreach (GiftGuide::all() as $guide): ?>
            <a class="bricks-gift-picker-item"
               href="<?= Html::encode(Url::to(['/lego/lego/gift-guide', 'slug' => $guide['slug']])) ?>"
               style="--gp-accent: <?= Html::encode($guide['accent']) ?>;">
                <b><?= Html::encode($guide['ageLabel']) ?></b>
                <span><?= Html::encode($guide['hint']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="bricks-gift-picker-links">
        <a class="bricks-gift-picker-all" href="<?= Html::encode(Url::to(GiftGuide::entryRoute())) ?>">
            <?= Html::encode(T::tr('All gift guides')) ?>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </a>
        <?php if (\frontend\components\AdventCalendars::isPromoWindow()): ?>
        <a class="bricks-gift-picker-all" href="<?= Html::encode(Url::to(['/lego/lego/advent-calendars'])) ?>">
            <?= Html::encode(T::tr('LEGO advent calendars {year}', ['year' => \frontend\components\AdventCalendars::seasonYear()])) ?>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
