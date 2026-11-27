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
    // Cookieless daily traffic counters (TrafficRecorder); HTML pages only.
    'on afterRequest'     => [\frontend\components\TrafficRecorder::class, 'onAfterRequest'],
    // Abuse (empty UA, blocked networks, rate limit) is answered before routing.
    'on beforeRequest'    => static function (): void {
        \frontend\components\BotGuard::enforce();
    },
    // Language is known after routing: point "home" links (logo, breadcrumbs)
    // at the current language's homepage instead of the English "/".
    'on beforeAction'     => static function (): void {
        Yii::$app->homeUrl = \frontend\components\LangUrl::to('/');
    },
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
            // Minified builds of the vendor bundles (the defaults point at the
            // unminified files).
            'bundles'         => [
                \yii\web\JqueryAsset::class                 => ['js' => ['jquery.min.js']],
                \yii\bootstrap5\BootstrapAsset::class       => ['css' => ['dist/css/bootstrap.min.css']],
                \yii\bootstrap5\BootstrapPluginAsset::class => ['js' => ['dist/js/bootstrap.bundle.min.js']],
            ],
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
        'response'     => [
            // Server-Timing: PHP time from request start to send. Compared with
            // the browser's TTFB it shows how long a request waited in the
            // php-fpm queue (TTFB - app) vs. how long the app itself took.
            'on beforeSend' => static function (\yii\base\Event $event): void {
                $start = (float)($_SERVER['REQUEST_TIME_FLOAT'] ?? 0);
                if ($start > 0 && $event->sender instanceof \yii\web\Response) {
                    $ms = (microtime(true) - $start) * 1000;
                    $db = \Yii::getLogger()->getDbProfiling();
                    $event->sender->headers->set(
                        'Server-Timing',
                        sprintf('app;dur=%.1f, db;dur=%.1f;desc="%d queries"', $ms, $db[1] * 1000, $db[0])
                    );
                }
                // Modal HTML fragments (…-modal) are not pages: keep them out
                // of the index even if a crawler reaches one despite robots.txt.
                if ($event->sender instanceof \yii\web\Response
                    && \Yii::$app->has('request', true)
                    && \Yii::$app->request instanceof \yii\web\Request
                    && preg_match('#-modal(/|$)#', (string)\Yii::$app->request->pathInfo)
                ) {
                    $event->sender->headers->set('X-Robots-Tag', 'noindex, nofollow');
                }
                // Temporary bot diagnostics (stops by itself, see params).
                if ($event->sender instanceof \yii\web\Response) {
                    \frontend\components\RequestFingerprintLog::record($event->sender);
                }
            },
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => array_merge(require __DIR__ . '/../../common/config/error-mail-target.php', [
                [
                    'class'   => FileTarget::class,
                    'levels'  => ['error', 'warning'],
                    // 404/400 from bots probing random URLs were ~100% of the
                    // log, each with a full $_SERVER dump (~60 MB in 3 weeks).
                    'except'  => ['yii\web\HttpException:4*'],
                    'logVars' => ['_GET', '_POST'],
                    'maskVars' => ['_POST.password', '_POST.LoginForm.password', '_POST.SignupForm.password'],
                ],
                [
                    // Audit trail of ManagementController console runner starts.
                    'class'      => FileTarget::class,
                    'levels'     => ['info'],
                    'categories' => ['adminConsole'],
                    'logFile'    => '@runtime/logs/admin-console.log',
                    'logVars'    => [],
                ],
                [
                    // Audit trail of ManagementController::actionDbQuery.
                    'class'      => FileTarget::class,
                    'levels'     => ['info'],
                    'categories' => ['dbQuery'],
                    'logFile'    => '@runtime/logs/db-query.log',
                    'logVars'    => [],
                ],
                [
                    'class'   => \common\components\log\DbLogTarget::class,
                    'levels'  => ['error', 'warning'],
                    'except'  => ['yii\db\*', 'yii\web\HttpException:4*'],
                    'logVars' => [],
                ],
            ]),
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager'   => [
            'class'                       => \codemix\localeurls\UrlManager::class,
            'languages'                   => ['en', 'pl', 'de', 'fr', 'es', 'it', 'ja'],
            // No Accept-Language redirects: every URL serves the language in its
            // path (Google advises against auto-redirecting by locale). Visitors
            // whose browser prefers another language get a suggestion card
            // (views/layouts/_language-suggest.php). The cookie from an explicit
            // choice still redirects (enableLanguagePersistence).
            'enableLanguageDetection'     => false,
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
                // Removed /zh version: let site/legacy-language redirect it
                // before language detection adds a prefix (see rules below).
                '#^site/legacy-language#'                => '#^/?zh(/|$)#',
                '#^autocomplete/#'                       => '#^/?autocomplete/#',
                '#^wishlist/(toggle|remove)$#'           => '#^/?wishlist/(toggle|remove)$#',
                '#^owned-set/(toggle|remove)$#'          => '#^/?owned-set/(toggle|remove)$#',
                '#^management/queue-offer-import$#'      => '#^/?management/queue-offer-import$#',
                '#^management/db-query$#'                => '#^/?management/db-query$#',
                '#^management/console-[a-z]+$#'          => '#^/?management/console-[a-z]+$#',
                '#^management/activity$#'                => '#^/?management/activity$#',
                '#^management/traffic(-data)?$#'         => '#^/?management/traffic(-data)?$#',
                '#^wizard/(save|load)$#'                 => '#^/?wizard/(save|load)$#',
                '#^review/(save-simple|save-detailed|delete)$#' => '#^/?review/(save-simple|save-detailed|delete)$#',
                '#^user/set-language/#'                  => '#^/?user/set-language/#',
            ],
            'enablePrettyUrl'             => true,
            'showScriptName'              => false,
            'rules'                       => [
                // Chinese version removed (Nov 2026): old /zh URLs 301 to the
                // same path in English so indexed pages keep their signals.
                'zh'                                            => 'site/legacy-language',
                'zh/<path:.*>'                                  => 'site/legacy-language',
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
                'privacy'                                       => 'site/privacy',
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
                // <sub> first: URL creation picks the first rule that fits, and with
                // <slug> first a subtheme link came out as ?sub=... (duplicate URLs).
                '<modules:lego>/<controler:theme>/<slug>/<sub>' => 'lego/theme/index',
                '<modules:lego>/<controler:theme>/<slug>'       => 'lego/theme/index',
                '<alias:lego>'                                  => 'lego/lego/index',
                'lego/on-sale'                                  => 'lego/lego/promo',
                'lego/magazines'                                => 'lego/lego/magazines',
                'lego/exclusive'                                => 'lego/lego/exclusive',
                'lego/retiring-soon'                            => 'lego/lego/retiring-soon',
                'lego/new'                                      => 'lego/lego/new',
                'lego/gifts'                                    => 'lego/lego/gifts',
                'lego/advent-calendars'                         => 'lego/lego/advent-calendars',
                'lego/<slug:(for-toddlers|for-kids|for-tweens|for-teens|for-adults|small-builds|big-builds)>' => 'lego/lego/audience',
                'lego/<slug:(' . implode('|', array_keys(\frontend\components\GiftGuide::SLUGS)) . ')>' => 'lego/lego/gift-guide',
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
