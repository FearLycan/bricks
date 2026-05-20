<?php

use common\models\SetMinifig;
use frontend\components\T;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View         $this
 * @var SetMinifig[] $minifigs
 */

if (empty($minifigs)) {
    return;
}
?>

<section class="bricks-section bricks-section--minifigs">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3 flex-wrap">
            <h2 class="bricks-section-title mb-0"><?= Html::encode(T::tr('Featured minifigures')) ?></h2>
        </div>
        <div class="bricks-minifig-grid">
            <?php foreach ($minifigs as $minifig): ?>
                <?= $this->render('_minifig-card', ['model' => $minifig]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
