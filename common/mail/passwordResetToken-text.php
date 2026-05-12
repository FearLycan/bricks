<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/reset-password', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->username ?>,

We received a request to reset the password for your <?= Yii::$app->name ?> account.

Reset your password by visiting the link below (valid for 1 hour):

<?= $resetLink ?>

If you did not request a password reset, no action is required.
