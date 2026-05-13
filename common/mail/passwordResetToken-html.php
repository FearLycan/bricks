<?php

use yii\helpers\Html;

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
$this->title = 'Reset your password';
?>

<!-- Icon -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td align="center" style="background:#eef2ff;border-radius:14px;width:52px;height:52px;text-align:center;line-height:52px;font-size:26px;">
            🔑
        </td>
    </tr>
</table>

<h2 style="margin:0 0 10px;font-size:24px;font-weight:700;color:#111827;letter-spacing:-.3px;">
    Reset your password
</h2>
<p style="margin:0 0 28px;font-size:15px;color:#6b7280;line-height:1.65;">
    Hi <strong style="color:#111827;"><?= Html::encode($user->username) ?></strong>,<br>
    We received a request to reset your password. Click the button below to choose a new one.
    This link expires in <strong style="color:#111827;"><?= Html::encode($expireLabel) ?></strong>.
</p>

<!-- CTA button -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
    <tr>
        <td style="border-radius:12px;background:#1a1a2e;">
            <a href="<?= Html::encode($resetLink) ?>"
               style="display:inline-block;padding:15px 36px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:12px;letter-spacing:.1px;">
                Reset password
            </a>
        </td>
    </tr>
</table>

<!-- Security note -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td style="background:#fff7ed;border-radius:10px;padding:14px 18px;border-left:3px solid #f97316;">
            <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.5;">
                Didn't request this? You can safely ignore this email — your password won't change.
            </p>
        </td>
    </tr>
</table>

<!-- Fallback URL -->
<p style="margin:0 0 5px;font-size:12px;color:#9ca3af;line-height:1.5;">
    Button not working? Copy and paste this URL:
</p>
<p style="margin:0;font-size:12px;word-break:break-all;">
    <a href="<?= Html::encode($resetLink) ?>" style="color:#1a1a2e;text-decoration:underline;"><?= Html::encode($resetLink) ?></a>
</p>
