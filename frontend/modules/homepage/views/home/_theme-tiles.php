<?php

use common\models\Theme;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View    $this
 * @var Theme[] $themes
 * @var string  $title
 */

if (empty($themes)) {
    return;
}

$title = $title ?? T::tr('Shop by theme');
?>

<section class="bricks-section bricks-section--themes">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3 flex-wrap">
            <h2 class="bricks-section-title mb-0"><?= Html::encode($title) ?></h2>
            <a href="<?= Url::to(['/lego']) ?>" class="bricks-section-see-all">
                <?= Html::encode(T::tr('Browse all themes')) ?>
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="bricks-theme-grid">
            <?php foreach ($themes as $theme):
                $img      = trim((string)$theme->img);
                $name     = (string)$theme->name;
                $setsText = $theme->sets_count !== null
                    ? T::tr('{n} sets', ['n' => (int)$theme->sets_count])
                    : null;
            ?>
                <a href="<?= Url::to("/lego/theme/{$theme->slug}") ?>" class="bricks-theme-tile">
                    <?php if ($img !== ''): ?>
                        <span class="bricks-theme-tile-media" style="background-image: url('<?= Html::encode($img) ?>');"></span>
                    <?php else: ?>
                        <span class="bricks-theme-tile-media bricks-theme-tile-media--fallback">
                            <span class="bricks-theme-tile-initial"><?= Html::encode(mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                        </span>
                    <?php endif; ?>
                    <span class="bricks-theme-tile-overlay"></span>
                    <span class="bricks-theme-tile-body">
                        <span class="bricks-theme-tile-name"><?= Html::encode($name) ?></span>
                        <?php if ($setsText !== null): ?>
                            <span class="bricks-theme-tile-meta"><?= Html::encode($setsText) ?></span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
