<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\PasswordResetRequestForm $model */

use frontend\components\T;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = T::tr('Forgot password');

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap');
$this->registerCss('
    .auth-page { min-height:calc(100vh - 220px);display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background-color:transparent; }
    .auth-card { background:#fff;border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.06),0 10px 40px -4px rgba(0,0,0,.1);padding:2.5rem 2.25rem;width:100%;max-width:400px;font-family:"Rubik",sans-serif; }
    .auth-title { font-family:"Rubik",sans-serif;font-size:1.6rem;font-weight:600;color:#111827;margin-bottom:.25rem; }
    .auth-subtitle { font-size:.875rem;color:#6b7280;margin-bottom:1.75rem; }
    .auth-card .form-control { font-family:"Rubik",sans-serif;border:1.5px solid #e5e7eb;border-radius:10px;padding:.65rem 1rem;font-size:.95rem;color:#111827;background-color:#fafafa;transition:border-color .2s,box-shadow .2s; }
    .auth-card .form-control:focus { border-color:#FFD700;box-shadow:0 0 0 3px rgba(255,215,0,.2);background-color:#fff; }
    .btn-auth { font-family:"Rubik",sans-serif;font-weight:500;font-size:.95rem;border-radius:10px;padding:.7rem 1.5rem;letter-spacing:.01em;background-color:#1a1a2e;border-color:#1a1a2e;color:#fff;transition:transform .15s; }
    .btn-auth:hover { background-color:#FFD700;border-color:#FFD700;color:#1a1a2e; }
    .btn-auth:active { transform:scale(.98); }
    .auth-divider { border:none;border-top:1px solid #f3f4f6;margin:1.5rem 0; }
    .auth-links { font-size:.82rem;color:#9ca3af;font-family:"Rubik",sans-serif; }
    .auth-links a { color:#1a1a2e;text-decoration:none;font-weight:500; }
    .auth-links a:hover { text-decoration:underline; }
    .auth-card .invalid-feedback { font-family:"Rubik",sans-serif;font-size:.8rem; }
');
?>
<div class="auth-page">
    <div class="auth-card">
        <div class="text-center mb-4">
            <?= Html::img('@web/images/logo-social.png', ['class' => 'mb-3', 'style' => 'width:80px;height:80px;object-fit:contain;', 'loading' => 'lazy', 'alt' => Yii::$app->name]) ?>
            <h1 class="auth-title"><?= Html::encode(T::tr('Forgot password?')) ?></h1>
            <p class="auth-subtitle"><?= Html::encode(T::tr("Enter your email and we'll send you a reset link.")) ?></p>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

        <?= $form->field($model, 'email', ['options' => ['class' => 'mb-4']])->textInput([
            'autofocus'   => true,
            'placeholder' => T::tr('Email address'),
            'type'        => 'email',
            'class'       => 'form-control',
        ])->label(T::tr('Email')) ?>

        <?= Html::submitButton(T::tr('Send reset link'), ['class' => 'btn btn-primary w-100 btn-auth', 'name' => 'reset-button']) ?>

        <?php ActiveForm::end(); ?>

        <hr class="auth-divider">
        <p class="text-center auth-links mb-0">
            <a href="<?= \yii\helpers\Url::to(['auth/login']) ?>">&larr; <?= Html::encode(T::tr('Back to sign in')) ?></a>
        </p>
    </div>
</div>
