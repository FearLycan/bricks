<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/verify-email', 'token' => $user->verification_token]);
?>
Hello <?= $user->username ?>,

Thank you for registering at <?= Yii::$app->name ?>!

Please verify your email address by visiting the link below:

<?= $verifyLink ?>

If you did not create an account, you can safely ignore this email.
