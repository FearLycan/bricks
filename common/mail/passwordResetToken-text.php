<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/reset-password', 'token' => $user->password_reset_token]);
$expireSeconds = (int)Yii::$app->params['user.passwordResetTokenExpire'];
if ($expireSeconds % 3600 === 0) {
    $count = $expireSeconds / 3600;
    $expireLabel = $count . ' ' . ($count === 1 ? 'hour' : 'hours');
} else {
    $count = max(1, (int)round($expireSeconds / 60));
    $expireLabel = $count . ' ' . ($count === 1 ? 'minute' : 'minutes');
}
?>
Hello <?= $user->username ?>,

We received a request to reset the password for your <?= Yii::$app->name ?> account.

Reset your password by visiting the link below (valid for <?= $expireLabel ?>):

<?= $resetLink ?>

If you did not request a password reset, no action is required.
