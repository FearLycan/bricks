<?php

use common\models\Set;
use frontend\components\T;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View  $this
 * @var Set[] $items
 */

if (empty($items)) {
    return;
}
?>

<section class="bricks-section bricks-section--slider bricks-section--recommendations">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3 flex-wrap">
            <div>
                <span class="bricks-section-eyebrow">
                    <i class="bi bi-stars me-1"></i><?= Html::encode(T::tr('Picked for you')) ?>
                </span>
                <h2 class="bricks-section-title mb-0"><?= Html::encode(T::tr('Recommended for you')) ?></h2>
            </div>
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
