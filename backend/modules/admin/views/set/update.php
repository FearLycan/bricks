<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Set $model */

$this->title = 'Update Set: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="set-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
