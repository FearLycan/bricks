<?php

use common\models\User;
use frontend\modules\homepage\HomepageModule;
use frontend\modules\lego\LegoModule;
use frontend\modules\product\ProductModule;
use yii\log\FileTarget;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id'                  => 'brick-app',
    'name'                => 'Brick Store',
    'timeZone'            => 'Europe/Warsaw',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute'        => 'homepage/home/index',
    'components'          => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'request'      => [
            'csrfParam' => '_csrf-brick',
        ],
        'user'         => [
            'identityClass'   => User::class,
            'enableAutoLogin' => true,
            'identityCookie'  => ['name' => '_brick-number', 'httpOnly' => true],
        ],
        'session'      => [
            'name' => 'brick-session',
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => FileTarget::class,
                    'levels' => ['error', 'warning'],
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
                '/'                   => 'homepage/home/index',
                //'product/<slug>'          => 'product/product/view',
                '<alias:products>'    => 'product/product/index',
                '<alias:themes>'      => 'lego/theme/index',
                '<alias:lego>'        => 'lego/lego/index',
                '<alias:lego>/<slug>' => 'lego/lego/view',
                //'product/<action>'        => 'product/product/<action>',
            ],
        ],
    ],
    'modules'             => [
        'homepage' => ['class' => HomepageModule::class,],
        'product'  => ['class' => ProductModule::class,],
        'lego'     => ['class' => LegoModule::class,],
    ],
    'params'              => $params,
];
