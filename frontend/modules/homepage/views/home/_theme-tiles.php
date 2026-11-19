<?php

use common\models\Theme;
use frontend\components\T;
use frontend\components\ThemePalette;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View    $this
 * @var Theme[] $themes
 * @var array<int,string|null> $covers theme id => product image (optional)
 * @var string  $title
 */

if (empty($themes)) {
    return;
}

$title = $title ?? T::tr('Shop by theme');
?>

<section class="bricks-section bricks-section--themes" id="themes">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3 flex-wrap">
            <h2 class="bricks-section-title mb-0"><?= Html::encode($title) ?></h2>
            <a href="<?= Url::to(['/lego']) ?>" class="bricks-section-see-all">
                <?= Html::encode(T::tr('Browse all themes')) ?>
                <i class="ph ph-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="bricks-theme-grid">
            <?php foreach ($themes as $theme):
                $img      = ($covers ?? [])[(int)$theme->id] ?? $theme->getTileImageUrl();
                $name     = (string)$theme->name;
                $setsText = $theme->sets_count !== null
                    ? T::tr('{n} sets', ['n' => (int)$theme->sets_count])
                    : null;
            ?>
                <?php [$accent, $soft] = ThemePalette::forSlug((string)$theme->slug); ?>
                <a href="<?= Url::to("/lego/theme/{$theme->slug}") ?>" class="bricks-ttile"
                   style="--tt-c: <?= Html::encode($accent) ?>; --tt-soft: <?= Html::encode($soft) ?>">
                    <span class="bricks-ttile-stage">
                        <?php if ($img !== null): ?>
                            <img src="<?= Html::encode($img) ?>" alt="" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="bricks-ttile-initial" aria-hidden="true"><?= Html::encode(mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="bricks-ttile-foot">
                        <span class="bricks-ttile-title"><?= Html::encode($name) ?></span>
                        <?php if ($setsText !== null): ?>
                            <span class="bricks-ttile-count"><?= Html::encode($setsText) ?></span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
