<?php

use frontend\components\T;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Full-width branding hero used by catalog filter pages (magazines, exclusive,
 * retiring-soon) and the homepage seasonal spotlight. DTO-driven so callers
 * decide all copy and styling.
 *
 * @var View         $this
 * @var string       $title
 * @var string|null  $eyebrow
 * @var string|null  $intro
 * @var string|null  $image            Web-relative path (e.g. 'images/categories/magazines.jpg').
 *                                     Missing files silently fall back to the gradient background.
 * @var string|null  $icon             Bootstrap Icons class name, rendered before the title.
 * @var string|null  $modifier         Modifier suffix appended to `bricks-page-hero--`.
 * @var bool         $showBreadcrumbs
 * @var array|string|null $ctaUrl      Optional CTA link (Url::to args or string).
 * @var string|null  $ctaLabel
 */

$title = (string)($title ?? '');
$eyebrow = isset($eyebrow) ? (string)$eyebrow : '';
$intro = isset($intro) ? (string)$intro : '';
$image = isset($image) ? trim((string)$image) : '';
$icon = isset($icon) ? trim((string)$icon) : '';
$modifier = isset($modifier) ? trim((string)$modifier) : '';
$showBreadcrumbs = !empty($showBreadcrumbs);
$ctaUrl = $ctaUrl ?? null;
$ctaLabel = isset($ctaLabel) ? (string)$ctaLabel : '';

$resolvedImage = null;
if ($image !== '') {
    if (preg_match('#^(https?:)?//#', $image)) {
        $resolvedImage = $image;
    } else {
        $relative = ltrim($image, '/');
        if (is_file(Yii::getAlias('@webroot') . '/' . $relative)) {
            $resolvedImage = Yii::getAlias('@web') . '/' . $relative;
        }
    }
}

$classes = ['bricks-page-hero'];
if ($resolvedImage !== null) {
    $classes[] = 'bricks-page-hero--image';
}
if ($modifier !== '') {
    $classes[] = 'bricks-page-hero--' . $modifier;
}

$heroStyle = $resolvedImage !== null
    ? ' style="background-image: url(\'' . Html::encode($resolvedImage) . '\');"'
    : '';
?>

<section class="<?= implode(' ', $classes) ?>"<?= $heroStyle ?>>
    <div class="container">
        <div class="bricks-page-hero-inner">
            <?php if ($showBreadcrumbs): ?>
                <?= Breadcrumbs::widget([
                        'links'        => $this->params['breadcrumbs'] ?? [],
                        'homeLink'     => ['label' => Html::encode(Yii::$app->name), 'url' => Yii::$app->homeUrl],
                        'encodeLabels' => false,
                        'options'      => ['class' => 'breadcrumb bricks-page-hero-breadcrumb'],
                ]) ?>
            <?php endif; ?>

            <?php if ($eyebrow !== ''): ?>
                <span class="bricks-page-hero-eyebrow"><?= Html::encode($eyebrow) ?></span>
            <?php endif; ?>

            <h1 class="bricks-page-hero-title">
                <?php if ($icon !== ''): ?>
                    <i class="<?= Html::encode($icon) ?> me-2"></i>
                <?php endif; ?>
                <?= Html::encode($title) ?>
            </h1>

            <?php if ($intro !== ''): ?>
                <p class="bricks-page-hero-lead"><?= Html::encode($intro) ?></p>
            <?php endif; ?>

            <?php if ($ctaUrl !== null && $ctaLabel !== ''): ?>
                <a href="<?= Html::encode(is_array($ctaUrl) ? Url::to($ctaUrl) : (string)$ctaUrl) ?>" class="bricks-page-hero-cta">
                    <?= Html::encode($ctaLabel) ?>
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
