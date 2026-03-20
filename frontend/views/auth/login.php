<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
$this->registerCss('
    .auth-signin-wrapper {
        min-height: calc(100vh - 220px);
    }

    .auth-signin-form {
        max-width: 360px;
        width: 100%;
    }

    .auth-logo {
        width: 172px;
        height: 172px;
        object-fit: contain;
    }
');
?>
<div class="auth-signin-wrapper d-flex align-items-center justify-content-center py-5">
    <main class="auth-signin-form text-center">
        <?= Html::img('@web/images/logo-social.png', [
                'class'   => 'auth-logo mb-4',
                'loading' => 'lazy',
                'alt'     => Yii::$app->name,
        ]) ?>

        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username')
                ->textInput([
                        'class'       => 'form-control mb-2',
                        'autofocus'   => true,
                        'placeholder' => 'Username',
                ])->label(false) ?>

        <?= $form->field($model, 'password')
                ->passwordInput([
                        'class'       => 'form-control mb-2',
                        'placeholder' => 'Password',
                ])->label(false) ?>

        <?= $form->field($model, 'rememberMe', [
                'template'     => '{input}{label}{error}',
                'options'      => ['class' => 'form-check text-start my-3'],
                'labelOptions' => ['class' => 'form-check-label'],
        ])->checkbox(['class' => 'form-check-input',]) ?>

        <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary w-100 py-2', 'name' => 'login-button']) ?>

        <!--
            <div class="small mt-3 text-muted">
                Forgot password? <?= Html::a('Reset it', ['site/request-password-reset']) ?>.
                <br>
                Need verification email? <?= Html::a('Resend', ['site/resend-verification-email']) ?>
            </div>
            -->

        <?php ActiveForm::end(); ?>

        <p class="mt-4 mb-0 text-body-secondary">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
    </main>
</div>
