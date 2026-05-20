<?php

use common\models\SetMinifig;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View       $this
 * @var SetMinifig $model
 */

$image = trim((string)$model->image);
$href  = Url::to("/lego/minifig/{$model->number}");
?>

<a href="<?= $href ?>" class="bricks-minifig-card">
    <span class="bricks-minifig-card-media">
        <?php if ($image !== ''): ?>
            <img src="<?= Html::encode($image) ?>"
                 alt="<?= Html::encode((string)$model->name) ?>"
                 loading="lazy"
                 class="bricks-minifig-card-image">
        <?php endif; ?>
    </span>
    <span class="bricks-minifig-card-body">
        <span class="bricks-minifig-card-name"><?= Html::encode((string)$model->name) ?></span>
        <span class="bricks-minifig-card-number"><?= Html::encode((string)$model->number) ?></span>
    </span>
</a>
