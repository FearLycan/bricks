<?php

use common\enums\StatusEnum;
use common\models\Set;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View           $this
 * @var common\models\Set      $model
 * @var yii\widgets\ActiveForm $form
 */

?>

<div class="set-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-4">
            <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'number_variant')->textInput() ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList(StatusEnum::options(), ['prompt' => 'Select status']) ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'theme_id')->dropDownList(Set::getAvailableThemesList(), ['prompt' => 'Select theme']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'subtheme_id')->dropDownList(Set::getAvailableSubthemesList(), ['prompt' => 'No subtheme']) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'year')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'pieces')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'minifigures')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'age')->textInput() ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'released')->dropDownList([1 => 'Yes', 0 => 'No'], ['prompt' => 'Unknown']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'price')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'rating')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'availability')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'brickset_url')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'dimensions')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 7]) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Create Set' : 'Save Changes', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
