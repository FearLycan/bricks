<?php

use backend\modules\admin\models\forms\AliExpressOfferImportForm;
use common\models\Set;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var Set $set
 * @var AliExpressOfferImportForm $form
 */

$this->title = 'Queue AliExpress Offer';
$this->params['breadcrumbs'][] = ['label' => 'Sets', 'url' => ['/admin/set/index']];
$this->params['breadcrumbs'][] = ['label' => $set->name ?? 'Set', 'url' => ['/admin/set/view', 'id' => $set->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="set-offer-import-aliexpress">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Back to set', ['/admin/set/view', 'id' => $set->id, '#' => 'offers'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <p class="text-body-secondary mb-3">
                Paste a full AliExpress product URL or a short <code>s.click.aliexpress.com</code> link. The importer worker will process queued links every few minutes.
            </p>

            <?php $activeForm = ActiveForm::begin(); ?>
            <?= $activeForm->field($form, 'offerUrl')->textInput([
                'maxlength' => true,
                'placeholder' => 'https://pl.aliexpress.com/item/1005011558663557.html',
            ]) ?>
            <div class="mt-3">
                <?= Html::submitButton('Queue Import', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
