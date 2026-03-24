<?php

use frontend\components\T;
use frontend\models\QueueOfferImportForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var QueueOfferImportForm $model
 */

?>
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?= T::tr('Queue AliExpress link') ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Html::encode(T::tr('Close')) ?>"></button>
        </div>
        <?php $form = ActiveForm::begin([
            'action' => Url::to(['/management/queue-offer-import']),
            'options' => [
                'id' => 'queue-offer-import-form',
                'class' => 'js-submit-modal-form',
                'data-modal-target' => '#mainModal',
            ],
        ]); ?>
            <div class="modal-body">
                <div class="alert d-none js-form-alert mb-3" role="alert"></div>
                <?= $form->field($model, 'setId')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'offerUrl', [
                    'errorOptions' => [
                        'class' => 'invalid-feedback js-field-error d-none',
                        'data-field' => 'offerUrl',
                    ],
                ])->textInput([
                    'placeholder' => 'https://pl.aliexpress.com/item/100xxxxxxx.html',
                ]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= T::tr('Cancel') ?></button>
                <button type="submit" class="btn btn-success js-submit-btn"><?= T::tr('Save') ?></button>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
