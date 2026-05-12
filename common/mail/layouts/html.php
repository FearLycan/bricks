<?php

use yii\helpers\Html;

/** @var \yii\web\View $this */
/** @var \yii\mail\MessageInterface $message */
/** @var string $content */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body style="margin:0;padding:0;background-color:#f1f3f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<?php $this->beginBody() ?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f1f3f8">
    <tr>
        <td align="center" style="padding:48px 16px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

                <!-- Brand header -->
                <tr>
                    <td align="center" style="padding-bottom:28px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="padding-bottom:10px;">
                                    <img src="<?= Yii::$app->urlManager->hostInfo . Yii::getAlias('@web') ?>/images/logo-social.png"
                                         alt="<?= Html::encode(Yii::$app->name) ?>"
                                         width="72" height="72"
                                         style="display:block;border:0;outline:none;text-decoration:none;border-radius:16px;" />
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <span style="font-size:18px;font-weight:700;color:#111827;letter-spacing:-.3px;">
                                        <?= Html::encode(Yii::$app->name) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background:#ffffff;border-radius:20px;box-shadow:0 4px 6px rgba(0,0,0,.05),0 10px 30px rgba(0,0,0,.08);">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">

                            <!-- Gradient top bar -->
                            <tr>
                                <td style="height:5px;background:#FFD700;border-radius:20px 20px 0 0;font-size:0;line-height:0;">&nbsp;</td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style="padding:40px 44px 32px;">
                                    <?= $content ?>
                                </td>
                            </tr>

                            <!-- Footer note inside card -->
                            <tr>
                                <td style="padding:0 44px 36px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="border-top:1px solid #f0f2f6;padding-top:20px;">
                                                <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                                                    This email was sent by <strong style="color:#6b7280;"><?= Html::encode(Yii::$app->name) ?></strong>.
                                                    If you didn't request this, you can safely ignore this message.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                <!-- Copyright -->
                <tr>
                    <td align="center" style="padding-top:28px;">
                        <p style="margin:0;font-size:12px;color:#9ca3af;">
                            &copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
