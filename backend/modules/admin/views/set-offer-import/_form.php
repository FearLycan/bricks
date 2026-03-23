<?php

use common\enums\SetOfferImportStatusEnum;
use common\models\SetOfferImport;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var SetOfferImport $model
 */
?>
<div class="set-offer-import-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row g-3">
        <div class="col-md-3">
            <?= $form->field($model, 'set_id')->textInput(['type' => 'number', 'min' => 1]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'status')->dropDownList(SetOfferImportStatusEnum::options(), ['prompt' => 'Select status']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'input_url')->textInput(['readonly' => true]) ?>
        </div>
    </div>

    <div class="mt-3">
        <?= Html::submitButton('Save Changes', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
