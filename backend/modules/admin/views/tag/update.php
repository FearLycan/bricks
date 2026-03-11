<?php

use common\models\Tag;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var Tag          $model
 */

$this->title = 'Update Tag: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

?>
<div class="tag-update">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Back to list', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
            'model' => $model,
    ]) ?>
</div>
