<?php

use common\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View           $this
 * @var User                   $model
 * @var string                 $password
 * @var yii\widgets\ActiveForm $form
 */
?>
<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList(User::getStatusOptions(), ['prompt' => 'Select status']) ?>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="new_password"><?= $model->isNewRecord ? 'Password' : 'New Password (optional)' ?></label>
            <?= Html::passwordInput('new_password', $password, ['class' => 'form-control', 'id' => 'new_password']) ?>
            <?php if ($model->hasErrors('password_hash')): ?>
                <div class="invalid-feedback d-block">
                    <?= Html::encode((string)current($model->getErrors('password_hash'))) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Create User' : 'Save Changes', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
