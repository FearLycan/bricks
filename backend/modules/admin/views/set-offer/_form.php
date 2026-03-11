<?php

use common\models\SetOffer;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View           $this
 * @var SetOffer               $model
 * @var array<int, string>     $storesList
 * @var yii\widgets\ActiveForm $form
 */
?>
<div class="set-offer-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row g-3">
        <div class="col-md-4">
            <?= $form->field($model, 'store_id')->dropDownList($storesList, ['prompt' => 'Select store']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'price')->textInput(['type' => 'number', 'min' => 0]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'currency_code')->textInput(['maxlength' => 3]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'availability')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'external_id')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'source')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <?= Html::activeHiddenInput($model, 'set_id') ?>
    <?= Html::activeHiddenInput($model, 'is_manual_override', ['value' => (int)$model->is_manual_override]) ?>
    <div class="mt-3">
        <?= Html::submitButton('Save Offer', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
