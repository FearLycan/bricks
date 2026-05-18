<?php

use common\components\Html;
use common\models\Set;
use frontend\components\T;
use yii\helpers\Url;

/** @var Set $set */
$loginUrl = Url::to(['/auth/login']);
$registerUrl = Url::to(['/auth/signup']);
?>
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content review-modal">
        <div class="modal-header border-0 pb-0">
            <h5 class="modal-title d-flex align-items-center gap-2">
                <i class="bi bi-stars text-warning"></i>
                <?= T::tr('Rate this set') ?>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Html::encode(T::tr('Close')) ?>"></button>
        </div>
        <div class="modal-body text-center">
            <div class="display-6 mb-2"><i class="bi bi-emoji-smile text-primary"></i></div>
            <h6 class="mb-2"><?= Html::encode($set->name) ?></h6>
            <p class="text-body-secondary mb-4">
                <?= T::tr('Sign in to share your opinion and help other builders make great choices.') ?>
            </p>
            <div class="d-grid gap-2 col-12 col-sm-8 mx-auto">
                <?= Html::a('<i class="bi bi-box-arrow-in-right me-1"></i>' . T::tr('Sign in to rate'), $loginUrl, ['class' => 'btn btn-primary']) ?>
                <?= Html::a(T::tr('Create an account'), $registerUrl, ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
</div>
