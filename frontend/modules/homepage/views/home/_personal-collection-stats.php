<?php

use common\models\Theme;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View       $this
 * @var array|null $stats
 */

if (!is_array($stats)) {
    return;
}

$setsOwned   = (int)($stats['sets_owned'] ?? 0);
$piecesTotal = (int)($stats['pieces_total'] ?? 0);
$wishlistSize = (int)($stats['wishlist_size'] ?? 0);
$topTheme    = $stats['top_theme'] ?? null;

if ($setsOwned === 0 && $wishlistSize === 0) {
    return;
}

$piecesFormatted = number_format($piecesTotal, 0, '.', ' ');
?>

<section class="bricks-section bricks-section--collection-stats">
    <div class="container">
        <div class="bricks-collection-stats">
            <div class="bricks-collection-stats-head">
                <h2 class="bricks-section-title mb-1"><?= Html::encode(T::tr('Your collection in numbers')) ?></h2>
                <p class="bricks-collection-stats-sub mb-0">
                    <?= Html::encode(T::tr('A quick snapshot of what you own and chase.')) ?>
                </p>
            </div>
            <div class="bricks-collection-stats-grid">
                <a class="bricks-collection-stats-tile" href="<?= Url::to(['/owned-set/index']) ?>">
                    <span class="bricks-collection-stats-icon"><i class="bi bi-box-seam-fill"></i></span>
                    <span class="bricks-collection-stats-value"><?= $setsOwned ?></span>
                    <span class="bricks-collection-stats-label"><?= Html::encode(T::tr('Owned sets')) ?></span>
                </a>
                <div class="bricks-collection-stats-tile">
                    <span class="bricks-collection-stats-icon"><i class="bi bi-bricks"></i></span>
                    <span class="bricks-collection-stats-value"><?= Html::encode($piecesFormatted) ?></span>
                    <span class="bricks-collection-stats-label"><?= Html::encode(T::tr('Total pieces')) ?></span>
                </div>
                <a class="bricks-collection-stats-tile" href="<?= Url::to(['/wishlist/index']) ?>">
                    <span class="bricks-collection-stats-icon"><i class="bi bi-heart-fill"></i></span>
                    <span class="bricks-collection-stats-value"><?= $wishlistSize ?></span>
                    <span class="bricks-collection-stats-label"><?= Html::encode(T::tr('On your wishlist')) ?></span>
                </a>
                <?php if ($topTheme instanceof Theme): ?>
                    <a class="bricks-collection-stats-tile bricks-collection-stats-tile--theme" href="<?= Url::to("/lego/theme/{$topTheme->slug}") ?>">
                        <span class="bricks-collection-stats-icon"><i class="bi bi-bookmark-star-fill"></i></span>
                        <span class="bricks-collection-stats-value"><?= Html::encode((string)$topTheme->name) ?></span>
                        <span class="bricks-collection-stats-label"><?= Html::encode(T::tr('Your top theme')) ?></span>
                    </a>
                <?php else: ?>
                    <div class="bricks-collection-stats-tile bricks-collection-stats-tile--empty">
                        <span class="bricks-collection-stats-icon"><i class="bi bi-bookmark"></i></span>
                        <span class="bricks-collection-stats-value">—</span>
                        <span class="bricks-collection-stats-label"><?= Html::encode(T::tr('Your top theme')) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
