<?php

use common\models\Set;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Reusable horizontal slider for a list of sets.
 *
 * @var View        $this
 * @var string      $title
 * @var Set[]       $items
 * @var string|null $seeAllUrl
 * @var string|null $seeAllLabel
 * @var string|null $modifierClass  Optional extra class on .bricks-section (e.g. brand variant)
 */

if (empty($items)) {
    return;
}

$seeAllUrl   = $seeAllUrl   ?? null;
$seeAllLabel = $seeAllLabel ?? T::tr('See all');
$modifierClass = $modifierClass ?? '';
?>

<section class="bricks-section bricks-section--slider <?= Html::encode($modifierClass) ?>">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3 flex-wrap">
            <h2 class="bricks-section-title mb-0"><?= $title ?></h2>
            <?php if ($seeAllUrl !== null): ?>
                <a href="<?= Html::encode(Url::to($seeAllUrl)) ?>" class="bricks-section-see-all">
                    <?= Html::encode($seeAllLabel) ?>
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            <?php endif; ?>
        </div>
        <div class="bricks-slider">
            <div class="bricks-slider-track">
                <?php foreach ($items as $item): ?>
                    <div class="bricks-slider-item">
                        <?= $this->render('_set-card', ['model' => $item]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
