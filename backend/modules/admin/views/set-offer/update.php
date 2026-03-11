<?php

use common\models\SetOffer;
use yii\helpers\Html;

/**
 * @var yii\web\View       $this
 * @var SetOffer           $model
 * @var array<int, string> $storesList
 */

$this->title = 'Update Offer';
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['/admin/set/index']];
$this->params['breadcrumbs'][] = ['label' => $model->set->name ?? 'Set', 'url' => ['/admin/set/view', 'id' => $model->set_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="set-offer-update">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Back to set', ['/admin/set/view', 'id' => $model->set_id, '#' => 'offers'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'storesList' => $storesList,
    ]) ?>
</div>
