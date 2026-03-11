<?php

use common\enums\StatusEnum;
use common\models\Theme;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View           $this
 * @var Theme                  $model
 * @var array<int, string>     $groupsList
 * @var array<int, string>     $parentsList
 * @var yii\widgets\ActiveForm $form
 */

?>
<div class="theme-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'group_id')->dropDownList($groupsList, ['prompt' => 'No group']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'parent_id')->dropDownList($parentsList, ['prompt' => 'No parent']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'sets_count')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'year_from')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'year_to')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'status')->dropDownList(StatusEnum::options(), ['prompt' => 'Select status']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'img')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'custom_css')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Create Theme' : 'Save Changes', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
