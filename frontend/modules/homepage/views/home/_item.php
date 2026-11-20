<?php

use common\models\Set;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $this  View
 * @var $model Set
 */

?>

<p>

</p>

<a href="<?= \frontend\components\LangUrl::to("/lego/{$model->slug}") ?>" class="text-decoration-none text-reset">
    <div class="card h-100">
        <img src="<?= $model->getMainImage()->url ?? "https://placehold.co/300x220?text={$model->number}" ?>" class="card-img-top img-fluid set-card-image" loading="lazy" alt="<?= Html::encode($model->name) ?>">
        <div class="card-body">
            <h3 class="card-title set-card-title">
                <?= Html::encode($model->name) ?>
            </h3>
        </div>
    </div>
</a>