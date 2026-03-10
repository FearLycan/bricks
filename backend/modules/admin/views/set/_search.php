<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\modules\admin\models\SetSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="set-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'number') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'slug') ?>

    <?= $form->field($model, 'theme_id') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'number_variant') ?>

    <?php // echo $form->field($model, 'minifigures') ?>

    <?php // echo $form->field($model, 'year') ?>

    <?php // echo $form->field($model, 'pieces') ?>

    <?php // echo $form->field($model, 'released') ?>

    <?php // echo $form->field($model, 'brickset_url') ?>

    <?php // echo $form->field($model, 'rating') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'age') ?>

    <?php // echo $form->field($model, 'dimensions') ?>

    <?php // echo $form->field($model, 'availability') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'subtheme_id') ?>

    <?php // echo $form->field($model, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
