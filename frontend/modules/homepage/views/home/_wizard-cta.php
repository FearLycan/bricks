<?php

use frontend\components\T;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 */
?>

<section class="bricks-section bricks-section--wizard">
    <div class="container">
        <button type="button"
                class="bricks-wizard-cta"
                data-bs-toggle="modal"
                data-bs-target="#wizardModal">
            <span class="bricks-wizard-cta-icon">
                <i class="bi bi-magic"></i>
            </span>
            <span class="bricks-wizard-cta-content">
                <span class="bricks-wizard-cta-eyebrow"><?= Html::encode(T::tr('In a minute')) ?></span>
                <span class="bricks-wizard-cta-title"><?= Html::encode(T::tr('Find your perfect set')) ?></span>
                <span class="bricks-wizard-cta-lead">
                    <?= Html::encode(T::tr('Answer a few questions — budget, age, theme — and we will match the best LEGO sets for you.')) ?>
                </span>
            </span>
            <span class="bricks-wizard-cta-action">
                <span><?= Html::encode(T::tr('Start the wizard')) ?></span>
                <i class="bi bi-arrow-right"></i>
            </span>
        </button>
    </div>
</section>
