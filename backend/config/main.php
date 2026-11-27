<?php

use backend\modules\admin\AdminModule;
use common\models\User;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

// Admin tools (ConsoleController) used to live in the frontend, so servers may still have
// their switches only in frontend/config/params-local.php. Backend params-local wins.
$frontendLocal = __DIR__ . '/../../frontend/config/params-local.php';
if (is_file($frontendLocal)) {
    foreach ((array)(require $frontendLocal) as $key => $value) {
        if (preg_match('/^(console|dbQuery)\./', (string)$key) && !array_key_exists($key, require __DIR__ . '/params-local.php')) {
            $params[$key] = $value;
        }
    }
}

return [
    'id'                  => 'app-backend',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute'        => 'admin/dashboard/index',
    'bootstrap'           => ['log'],
    'components'          => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'request'      => [
            'csrfParam' => '_csrf-backend',
        ],
        'user'         => [
            'identityClass'   => User::class,
            'enableAutoLogin' => true,
            'identityCookie'  => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session'      => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
                [
                    // Audit trail of admin/console/run (command starts and refusals).
                    'class'      => \yii\log\FileTarget::class,
                    'levels'     => ['info'],
                    'categories' => ['adminConsole'],
                    'logFile'    => '@runtime/logs/admin-console.log',
                    'logVars'    => [],
                ],
                [
                    // Audit trail of admin/console/db-query.
                    'class'      => \yii\log\FileTarget::class,
                    'levels'     => ['info'],
                    'categories' => ['dbQuery'],
                    'logFile'    => '@runtime/logs/db-query.log',
                    'logVars'    => [],
                ],
                [
                    'class'  => \common\components\log\DbLogTarget::class,
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\db\*', 'yii\web\HttpException:4*'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
            ],
        ],
        'frontendUrlManager' => [
            'class' => \yii\web\UrlManager::class,
            'hostInfo' => rtrim((string)($params['frontend.baseUrl'] ?? ''), '/'),
            'baseUrl' => '',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'modules'             => [
        'admin' => ['class' => AdminModule::class,],
    ],
    'params'              => $params,
];
