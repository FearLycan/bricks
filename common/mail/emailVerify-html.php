<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/verify-email', 'token' => $user->verification_token]);
$this->title = 'Confirm your email address';
?>

<h2 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#111827;letter-spacing:-.3px;">
    Verify your email
</h2>
<p style="margin:0 0 28px;font-size:15px;color:#6b7280;line-height:1.65;">
    Hey <strong style="color:#111827;"><?= Html::encode($user->username) ?></strong>,<br>
    Welcome to <?= Html::encode(Yii::$app->name) ?>! Click the button below to confirm your email address and activate your account.
</p>

<!-- CTA button -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr>
        <td style="border-radius:12px;background:#1a1a2e;">
            <a href="<?= Html::encode($verifyLink) ?>"
               style="display:inline-block;padding:15px 36px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:12px;letter-spacing:.1px;">
                Verify email address
            </a>
        </td>
    </tr>
</table>

<!-- Info note -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td style="background:#f8fafc;border-radius:10px;padding:14px 18px;border-left:3px solid #FFD700;">
            <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.5;">
                This link expires in <strong style="color:#111827;">24 hours</strong>. If it expires, you can request a new one from the sign-in page.
            </p>
        </td>
    </tr>
</table>

<!-- Fallback URL -->
<p style="margin:0 0 5px;font-size:12px;color:#9ca3af;line-height:1.5;">
    Button not working? Copy and paste this URL:
</p>
<p style="margin:0;font-size:12px;word-break:break-all;">
    <a href="<?= Html::encode($verifyLink) ?>" style="color:#1a1a2e;text-decoration:underline;"><?= Html::encode($verifyLink) ?></a>
</p>
