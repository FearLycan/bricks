<?php

use common\models\User;
use frontend\components\LanguageSyncBootstrap;
use frontend\modules\homepage\HomepageModule;
use frontend\modules\lego\LegoModule;
use frontend\modules\product\ProductModule;
use frontend\modules\review\ReviewModule;
use frontend\modules\user\UserModule;
use frontend\modules\wizard\WizardModule;
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
    'language'            => 'en',
    'sourceLanguage'      => 'en',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute'        => 'homepage/home/index',
    'components'          => [
        'i18n'         => [
            'translations' => [
                'app*' => [
                    'class'          => \yii\i18n\PhpMessageSource::class,
                    'basePath'       => '@frontend/messages',
                    'sourceLanguage' => 'en',
                    'fileMap'        => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],
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
            'class'                       => \codemix\localeurls\UrlManager::class,
            'languages'                   => ['en', 'pl', 'de', 'fr', 'es', 'it', 'ja', 'zh'],
            'enableLanguageDetection'     => true,
            'enableLanguagePersistence'   => true,
            'enableDefaultLanguageUrlCode' => false,
            'languageCookieDuration'      => 60 * 60 * 24 * 365,
            // Endpoints that bypass language processing entirely. Use ONLY for endpoints
            // that:
            //   - return JSON (no translated user-facing strings rendered)
            //   - or operate on data that has no language (autocomplete results from DB)
            // Modal/HTML endpoints (e.g. *-modal) MUST stay out of this list so the
            // rendered markup uses the active language.
            'ignoreLanguageUrlPatterns'   => [
                '#^autocomplete/#'                       => '#^/?autocomplete/#',
                '#^wishlist/(toggle|remove)$#'           => '#^/?wishlist/(toggle|remove)$#',
                '#^owned-set/(toggle|remove)$#'          => '#^/?owned-set/(toggle|remove)$#',
                '#^management/queue-offer-import$#'      => '#^/?management/queue-offer-import$#',
                '#^wizard/(save|load)$#'                 => '#^/?wizard/(save|load)$#',
                '#^review/(save-simple|save-detailed|delete)$#' => '#^/?review/(save-simple|save-detailed|delete)$#',
                '#^user/set-language/#'                  => '#^/?user/set-language/#',
            ],
            'enablePrettyUrl'             => true,
            'showScriptName'              => false,
            'rules'                       => [
                ''                                              => 'homepage/home/index',
                'feed'                                          => 'feed/index',
                'feed.xml'                                      => 'feed/xml',
                'feed.rss'                                      => 'feed/rss',
                'glossary'                                      => 'site/glossary',
                'interests'                                     => 'site/interests',
                'faq'                                           => 'site/faq',
                'help'                                          => 'site/faq',
                'contact'                                       => 'site/contact',
                'about'                                         => 'site/about',
                'wizard/save'                                   => 'wizard/default/save',
                'wizard/load'                                   => 'wizard/default/load',
                'review/save-simple'                            => 'review/default/save-simple',
                'review/save-detailed'                          => 'review/default/save-detailed',
                'review/delete'                                 => 'review/default/delete',
                'user'                                          => 'user/profile/index',
                'user/profile'                                  => 'user/profile/index',
                'user/settings'                                 => 'user/settings/index',
                'user/set-language/<lang:[a-z]{2}>'             => 'user/settings/set-language',
                '<alias:products>'                              => 'product/product/index',
                '<modules:lego>/<controler:theme>/<slug>'       => 'lego/theme/index',
                '<modules:lego>/<controler:theme>/<slug>/<sub>' => 'lego/theme/index',
                '<alias:lego>'                                  => 'lego/lego/index',
                'lego/on-sale'                                  => 'lego/lego/promo',
                'lego/magazines'                                => 'lego/lego/magazines',
                'lego/exclusive'                                => 'lego/lego/exclusive',
                'lego/retiring-soon'                            => 'lego/lego/retiring-soon',
                'lego/new'                                      => 'lego/lego/new',
                'lego/<slug:(for-toddlers|for-kids|for-tweens|for-teens|for-adults|small-builds|big-builds)>' => 'lego/lego/audience',
                '<alias:lego>/minifig/<number:[A-Za-z0-9\\-]+>' => 'lego/lego/minifig',
                'lego/tag/<slug:[a-z0-9-]+>'                    => 'lego/lego/tag',
                '<modules:lego>/<slug>'                         => 'lego/lego/view',
            ],
        ],
        'backendUrlManager' => [
            'class' => \yii\web\UrlManager::class,
            'hostInfo' => rtrim((string)($params['backend.baseUrl'] ?? ''), '/'),
            'baseUrl' => '',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'modules'             => [
        'homepage' => ['class' => HomepageModule::class,],
        'product'  => ['class' => ProductModule::class,],
        'lego'     => ['class' => LegoModule::class,],
        'wizard'   => ['class' => WizardModule::class,],
        'review'   => ['class' => ReviewModule::class,],
        'user'     => ['class' => UserModule::class,],
    ],
    'params'              => $params,
];
