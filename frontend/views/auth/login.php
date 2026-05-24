<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = T::tr('Sign in to BrickAtlas — LEGO® Price Tracker');
$this->params['metaDescription'] = T::tr('Sign in to BrickAtlas to access your LEGO wishlist, set reviews, owned-set collection and personalized price-drop notifications.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/auth/login']);

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap');

$this->registerCss('
    .auth-page {
        min-height: calc(100vh - 220px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background-color: transparent;
    }

    .auth-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.06), 0 10px 40px -4px rgba(0,0,0,.1);
        padding: 2.5rem 2.25rem;
        width: 100%;
        max-width: 400px;
        font-family: "Rubik", sans-serif;
    }

    .auth-logo {
        width: 80px;
        height: 80px;
        object-fit: contain;
    }

    .auth-title {
        font-family: "Rubik", sans-serif;
        font-size: 1.6rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: .25rem;
    }

    .auth-subtitle {
        font-size: .875rem;
        color: #6b7280;
        margin-bottom: 1.75rem;
    }

    .auth-card .form-control {
        font-family: "Rubik", sans-serif;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: .65rem 1rem;
        font-size: .95rem;
        color: #111827;
        background-color: #fafafa;
        transition: border-color .2s, box-shadow .2s;
    }

    .auth-card .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 3px rgba(255,215,0,.2);
        background-color: #fff;
    }

    .auth-card .field-loginform-password {
        position: relative;
    }

    .pass-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #9ca3af;
        line-height: 1;
        z-index: 5;
    }

    .pass-toggle:hover { color: #6366f1; }

    .auth-card .field-loginform-password .form-control {
        padding-right: 2.75rem;
    }

    .pass-field .form-control.is-valid,
    .pass-field .form-control.is-invalid {
        background-image: none;
    }

    .auth-card .form-check-input:checked {
        background-color: #1a1a2e;
        border-color: #1a1a2e;
    }

    .auth-card .form-check-label {
        font-size: .875rem;
        color: #374151;
        font-family: "Rubik", sans-serif;
    }

    .btn-auth {
        font-family: "Rubik", sans-serif;
        font-weight: 500;
        font-size: .95rem;
        border-radius: 10px;
        padding: .7rem 1.5rem;
        letter-spacing: .01em;
        background-color: #1a1a2e;
        border-color: #1a1a2e;
        color: #fff;
        transition: transform .15s, box-shadow .15s;
    }

    .btn-auth:hover { background-color: #FFD700; border-color: #FFD700; color: #1a1a2e; }
    .btn-auth:active { transform: scale(.98); }

    .auth-divider {
        border: none;
        border-top: 1px solid #f3f4f6;
        margin: 1.5rem 0;
    }

    .auth-links {
        font-size: .82rem;
        color: #9ca3af;
        font-family: "Rubik", sans-serif;
    }

    .auth-links a {
        color: #1a1a2e;
        text-decoration: none;
        font-weight: 500;
    }

    .auth-links a:hover { text-decoration: underline; }

    .auth-card .invalid-feedback { font-family: "Rubik", sans-serif; font-size: .8rem; }
');
?>
<div class="auth-page">
    <div class="auth-card">
        <div class="text-center mb-4">
            <?= Html::img('@web/images/logo-social.png', [
                'class'   => 'auth-logo mb-3',
                'loading' => 'lazy',
                'alt'     => Yii::$app->name,
            ]) ?>
            <h1 class="auth-title"><?= Html::encode(T::tr('Welcome back')) ?></h1>
            <p class="auth-subtitle"><?= Html::encode(T::tr('Sign in to continue')) ?></p>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username', ['options' => ['class' => 'mb-3']])->textInput([
            'autofocus'   => true,
            'placeholder' => T::tr('Username'),
            'class'       => 'form-control',
        ])->label(T::tr('Username')) ?>

        <div class="mb-3">
            <?= $form->field($model, 'password', [
                'options' => ['class' => 'mb-0'],
                'template' => '{label}<div class="pass-field" style="position:relative">{input}<button type="button" class="pass-toggle" id="passToggle" tabindex="-1" aria-label="' . Html::encode(T::tr('Show password')) . '">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg></button></div>{error}',
            ])->passwordInput([
                'placeholder' => T::tr('Password'),
                'class'       => 'form-control',
                'id'          => 'loginform-password',
            ])->label(T::tr('Password')) ?>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <?= $form->field($model, 'rememberMe', [
                'template'     => '{input}{label}',
                'options'      => ['class' => 'form-check mb-0 ps-0'],
                'labelOptions' => ['class' => 'form-check-label'],
            ])->checkbox(['class' => 'form-check-input']) ?>

            <a href="<?= \yii\helpers\Url::to(['auth/request-password-reset']) ?>" class="text-decoration-none" style="font-size:.82rem; color:#1a1a2e; font-family:'Rubik',sans-serif; font-weight:500;"><?= Html::encode(T::tr('Forgot password?')) ?></a>
        </div>

        <?= Html::submitButton(T::tr('Sign in'), [
            'class' => 'btn btn-primary w-100 btn-auth',
            'name'  => 'login-button',
        ]) ?>

        <?php ActiveForm::end(); ?>

        <hr class="auth-divider">

        <p class="text-center auth-links mb-0">
            <?= Html::encode(T::tr("Don't have an account?")) ?>
            <a href="<?= \yii\helpers\Url::to(['auth/signup']) ?>"><?= Html::encode(T::tr('Register now')) ?></a>
        </p>
        <p class="text-center auth-links mt-2 mb-0">
            <?= Html::encode(T::tr("Didn't receive verification email?")) ?>
            <a href="<?= \yii\helpers\Url::to(['auth/resend-verification-email']) ?>"><?= Html::encode(T::tr('Resend it')) ?></a>
        </p>
    </div>
</div>

<?php $this->registerJs('
    var toggleBtn  = document.getElementById("passToggle");
    var passInput  = document.getElementById("loginform-password");
    var eyeOpen    = \'<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>\';
    var eyeClosed  = \'<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>\';

    toggleBtn.addEventListener("click", function() {
        var isPass = passInput.type === "password";
        passInput.type = isPass ? "text" : "password";
        toggleBtn.innerHTML = isPass ? eyeClosed : eyeOpen;
    });
'); ?>
