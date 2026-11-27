<?php

/**
 * Log target that mails PHP errors (level 'error' only; 404s and other client 4xx excluded)
 * to params['alerts.email'], at most one mail per 15 min. Off while alerts.email is empty
 * or alerts.mailErrors is false. Used by frontend/config/main.php and console/config/main.php.
 *
 * @var array $params
 */
$to = trim((string)($params['alerts.email'] ?? ''));
if (empty($params['alerts.mailErrors']) || $to === '' || str_ends_with($to, '@example.com')) {
    return [];
}

return [[
    'class'    => \common\components\log\ThrottledEmailTarget::class,
    'mailer'   => 'mailer',
    'levels'   => ['error'],
    'except'   => ['yii\web\HttpException:4*', 'yii\web\NotFoundHttpException*', 'yii\web\BadRequestHttpException*'],
    'logVars'  => [],
    'interval' => 900,
    'message'  => [
        'from'    => [(string)($params['senderEmail'] ?? $to)],
        'to'      => [$to],
        'subject' => '[BrickAtlas] PHP error',
    ],
]];
