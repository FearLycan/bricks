<?php

use common\models\Theme;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View       $this
 * @var Theme|null $theme
 */

if (!$theme instanceof Theme) {
    return;
}

$img         = trim((string)$theme->img);
$description = trim((string)$theme->description);
$setsCount   = $theme->sets_count !== null ? (int)$theme->sets_count : null;
$themeUrl    = Url::to("/lego/theme/{$theme->slug}");
?>

<section class="bricks-spotlight"
         <?php if ($img !== ''): ?>style="background-image: linear-gradient(120deg, rgba(15, 23, 42, 0.78) 0%, rgba(15, 23, 42, 0.35) 55%, rgba(15, 23, 42, 0.05) 100%), url('<?= Html::encode($img) ?>');"<?php endif; ?>>
    <div class="container">
        <div class="bricks-spotlight-inner">
            <span class="bricks-spotlight-eyebrow"><?= Html::encode(T::tr('Theme spotlight')) ?></span>
            <h2 class="bricks-spotlight-title"><?= Html::encode((string)$theme->name) ?></h2>
            <?php if ($description !== ''): ?>
                <p class="bricks-spotlight-description">
                    <?= Html::encode(mb_strimwidth($description, 0, 220, '…', 'UTF-8')) ?>
                </p>
            <?php endif; ?>
            <div class="bricks-spotlight-meta">
                <?php if ($setsCount !== null): ?>
                    <span><i class="bi bi-box-seam me-1"></i><?= T::tr('{n} sets', ['n' => $setsCount]) ?></span>
                <?php endif; ?>
                <?php if ($theme->year_from): ?>
                    <span><i class="bi bi-calendar3 me-1"></i><?= Html::encode((string)$theme->year_from) ?><?= $theme->year_to ? '–' . Html::encode((string)$theme->year_to) : '' ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= $themeUrl ?>" class="bricks-spotlight-cta">
                <?= Html::encode(T::tr('Explore the theme')) ?>
                <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
