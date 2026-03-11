<?php

use yii\helpers\Html;

/**
 * @var yii\web\View      $this
 * @var common\models\Set $model
 */

$this->title = 'Create Set';
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="set-create">

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
