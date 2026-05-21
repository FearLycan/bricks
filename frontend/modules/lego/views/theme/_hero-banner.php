<?php

use common\models\Theme;
use frontend\components\T;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use yii\web\View;

/**
 * Full-width branding hero for a theme / subtheme page.
 *
 * @var View       $this
 * @var Theme      $theme    The top-level theme.
 * @var Theme|null $subTheme The active subtheme, when one is selected.
 * @var string     $intro    Short descriptive paragraph.
 */

$model = $subTheme ?? $theme;

// Wide hero image of the active model; fall back to the parent theme's image
// for subthemes that have none of their own.
$heroImage = $model->getHeroImageUrl();
if ($heroImage === null && $subTheme !== null) {
    $heroImage = $theme->getHeroImageUrl();
}

// Eyebrow: parent theme name on subtheme pages, otherwise the theme group.
$eyebrow = $subTheme !== null
    ? (string)$theme->name
    : (string)($theme->group->name ?? '');

$setsCount = (int)$model->sets_count;
$customCss = trim((string)$model->custom_css);

$heroStyle = $heroImage !== null
    ? ' style="background-image: url(\'' . Html::encode($heroImage) . '\');"'
    : '';
?>

<section class="bricks-theme-hero<?= $heroImage !== null ? ' bricks-theme-hero--image' : '' ?>"<?= $heroStyle ?>>
    <div class="container">
        <div class="bricks-theme-hero-inner">
            <?= Breadcrumbs::widget([
                    'links'        => $this->params['breadcrumbs'] ?? [],
                    'homeLink'     => ['label' => Html::encode(Yii::$app->name), 'url' => Yii::$app->homeUrl],
                    'encodeLabels' => false,
                    'options'      => ['class' => 'breadcrumb bricks-theme-hero-breadcrumb'],
            ]) ?>

            <?php if ($eyebrow !== ''): ?>
                <span class="bricks-theme-hero-eyebrow"><?= Html::encode($eyebrow) ?></span>
            <?php endif; ?>

            <h1 class="bricks-theme-hero-title"><?= Html::encode((string)$model->name) ?></h1>

            <?php if ($intro !== ''): ?>
                <p class="bricks-theme-hero-lead"><?= Html::encode($intro) ?></p>
            <?php endif; ?>

            <?php if ($setsCount > 0): ?>
                <span class="bricks-theme-hero-meta">
                    <i class="bi bi-boxes"></i>
                    <?= T::tr('{n} sets', ['n' => $setsCount]) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($customCss !== ''): ?>
    <style><?= $customCss ?></style>
<?php endif; ?>
