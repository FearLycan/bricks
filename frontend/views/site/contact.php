<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\ContactForm $model */

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = T::tr('Contact BrickAtlas — Questions and Feedback');
$this->params['metaDescription'] = T::tr('Get in touch with the BrickAtlas team. Send questions, business inquiries or bug reports — we usually reply within 24 hours.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/site/contact']);
$this->params['breadcrumbs'][] = T::tr('Contact');

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap');
$this->registerCss('
    .contact-page {
        min-height: calc(100vh - 220px);
        padding: 3rem 1rem 4rem;
    }

    .contact-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.06), 0 10px 40px -4px rgba(0,0,0,.1);
        overflow: hidden;
        font-family: "Rubik", sans-serif;
    }

    .contact-header {
        background: #1a1a2e;
        padding: 2.5rem 2.5rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .contact-header::after {
        content: "";
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 4px;
        background: #FFD700;
    }

    .contact-header-title {
        font-family: "Rubik", sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 .4rem;
        letter-spacing: -.02em;
    }

    .contact-header-sub {
        font-size: .9rem;
        color: rgba(255,255,255,.6);
        margin: 0;
        font-weight: 400;
    }

    .contact-body {
        display: grid;
        grid-template-columns: 1fr 1.6fr;
        gap: 0;
    }

    @media (max-width: 767px) {
        .contact-body { grid-template-columns: 1fr; }
    }

    .contact-info {
        background: #f8f9fc;
        padding: 2.25rem 2rem;
        border-right: 1px solid #f0f0f0;
    }

    @media (max-width: 767px) {
        .contact-info { border-right: none; border-bottom: 1px solid #f0f0f0; }
    }

    .contact-info-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #9ca3af;
        margin-bottom: 1.25rem;
    }

    .contact-info-item {
        display: flex;
        align-items: flex-start;
        gap: .875rem;
        margin-bottom: 1.5rem;
    }

    .contact-info-icon {
        width: 38px;
        height: 38px;
        background: #1a1a2e;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFD700;
        font-size: .95rem;
        flex-shrink: 0;
    }

    .contact-info-text strong {
        display: block;
        font-size: .85rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: .1rem;
    }

    .contact-info-text span {
        font-size: .82rem;
        color: #6b7280;
    }

    .contact-form-wrap {
        padding: 2.25rem 2.5rem;
    }

    @media (max-width: 767px) {
        .contact-form-wrap { padding: 1.75rem 1.5rem; }
    }

    .contact-form-wrap .form-label {
        font-family: "Rubik", sans-serif;
        font-size: .82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .4rem;
    }

    .contact-form-wrap .form-control {
        font-family: "Rubik", sans-serif;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: .65rem 1rem;
        font-size: .9rem;
        color: #111827;
        background: #fafafa;
        transition: border-color .2s, box-shadow .2s;
    }

    .contact-form-wrap .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 3px rgba(255,215,0,.2);
        background: #fff;
        outline: none;
    }

    .contact-form-wrap textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .contact-form-wrap .invalid-feedback {
        font-family: "Rubik", sans-serif;
        font-size: .78rem;
    }

    .btn-contact {
        font-family: "Rubik", sans-serif;
        font-weight: 500;
        font-size: .95rem;
        border-radius: 10px;
        padding: .7rem 2rem;
        background-color: #1a1a2e;
        border-color: #1a1a2e;
        color: #fff;
        transition: background-color .18s, border-color .18s, color .18s, transform .15s;
        letter-spacing: .01em;
    }

    .btn-contact:hover {
        background-color: #FFD700;
        border-color: #FFD700;
        color: #1a1a2e;
    }

    .btn-contact:active { transform: scale(.98); }


');
?>

<div class="contact-page">
    <div class="container" style="max-width:840px;">
        <div class="contact-card">

            <div class="contact-header">
                <h1 class="contact-header-title"><?= Html::encode(T::tr('Get in touch')) ?></h1>
                <p class="contact-header-sub"><?= Html::encode(T::tr("Have a question or business inquiry? We'll get back to you shortly.")) ?></p>
            </div>

            <div class="contact-body">

                <div class="contact-info">
                    <p class="contact-info-label"><?= Html::encode(T::tr('Contact info')) ?></p>

                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="bi bi-clock"></i></div>
                        <div class="contact-info-text">
                            <strong><?= Html::encode(T::tr('Response time')) ?></strong>
                            <span><?= Html::encode(T::tr('Usually within 24 hours')) ?></span>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="bi bi-globe2"></i></div>
                        <div class="contact-info-text">
                            <strong><?= Html::encode(T::tr('Platform')) ?></strong>
                            <span><?= Html::encode(Yii::$app->name) ?></span>
                        </div>
                    </div>
                </div>

                <div class="contact-form-wrap">
                    <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'name', ['options' => ['class' => 'mb-0']])->textInput([
                                'autofocus'   => true,
                                'placeholder' => T::tr('Your name'),
                                'class'       => 'form-control',
                            ])->label(T::tr('Name')) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'email', ['options' => ['class' => 'mb-0']])->textInput([
                                'type'        => 'email',
                                'placeholder' => T::tr('Email address'),
                                'class'       => 'form-control',
                            ])->label(T::tr('Email')) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'subject', ['options' => ['class' => 'mb-0']])->textInput([
                            'placeholder' => T::tr('Subject'),
                            'class'       => 'form-control',
                        ])->label(T::tr('Subject')) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'body', ['options' => ['class' => 'mb-0']])->textarea([
                            'rows'        => 5,
                            'placeholder' => T::tr('Your message...'),
                            'class'       => 'form-control',
                        ])->label(T::tr('Message')) ?>
                    </div>

                    <?= Html::submitButton(T::tr('Send message') . ' <i class="bi bi-arrow-right ms-1"></i>', [
                        'class'          => 'btn btn-contact',
                        'name'           => 'contact-button',
                        'encode'         => false,
                    ]) ?>

                    <?php ActiveForm::end(); ?>
                </div>

            </div>
        </div>
    </div>
</div>
