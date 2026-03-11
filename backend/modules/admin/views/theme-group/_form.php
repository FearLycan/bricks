<?php

use common\enums\StatusEnum;
use common\models\ThemeGroup;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View           $this
 * @var ThemeGroup             $model
 * @var yii\widgets\ActiveForm $form
 */

?>
<div class="theme-group-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-8">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList(StatusEnum::options(), ['prompt' => 'Select status']) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Create Theme Group' : 'Save Changes', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
