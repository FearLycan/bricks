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
    'name'                => 'BrickAtlas',
    'timeZone'            => 'Europe/Warsaw',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute'        => 'lego/lego/index',
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
            'loginUrl'        => ['auth/login'],
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
                '<alias:products>'                              => 'product/product/index',
                '<modules:lego>/<controler:theme>/<slug>'       => 'lego/theme/index',
                '<modules:lego>/<controler:theme>/<slug>/<sub>' => 'lego/theme/index',
                '<alias:lego>'                                  => 'lego/lego/index',
                '<alias:lego>/minifig/<number:[A-Za-z0-9\\-]+>' => 'lego/lego/minifig',
                '<modules:lego>/<slug>'                         => 'lego/lego/view',
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
