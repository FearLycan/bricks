<?php

use common\schema\factory\BreadcrumbListSchemaFactory;
use common\schema\factory\OrganizationSchemaFactory;
use common\schema\JsonLdRenderer;
use common\widgets\Alert;
use frontend\assets\AppAsset;
use frontend\components\SeoHelper;
use frontend\components\T;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View   $this
 * @var string $content
 */

AppAsset::register($this);

$breadcrumbLinks = $this->params['breadcrumbs'] ?? [];
$homeBreadcrumb = [
        'label' => Html::encode(Yii::$app->name),
        'url'   => Yii::$app->homeUrl,
];

$schemaGraph = [
        OrganizationSchemaFactory::fromParams(),
        BreadcrumbListSchemaFactory::fromView($breadcrumbLinks, $homeBreadcrumb, (string)$this->title),
];

$this->params['socialImage'] = Url::to('/images/logo-social.png', true);

$pageTitle = SeoHelper::normalizeText((string)$this->title);
$metaDescription = trim((string)($this->params['metaDescription'] ?? ''));
$canonicalUrl = trim((string)($this->params['canonicalUrl'] ?? Url::current([], true)));
$robots = trim((string)($this->params['robots'] ?? 'index,follow'));
$socialTitle = trim((string)($this->params['socialTitle'] ?? $pageTitle));
$socialDescription = trim((string)($this->params['socialDescription'] ?? $metaDescription));
$socialImage = trim((string)($this->params['socialImage'] ?? ''));
$ogType = trim((string)($this->params['ogType'] ?? 'website'));

if ($metaDescription === '') {
    $metaDescription = SeoHelper::defaultMetaDescription();
}

if ($socialDescription === '') {
    $socialDescription = $metaDescription;
}

$this->registerMetaTag(['name' => 'description', 'content' => $metaDescription], 'description');
$this->registerMetaTag(['name' => 'robots', 'content' => $robots], 'robots');
$this->registerLinkTag(['rel' => 'canonical', 'href' => $canonicalUrl], 'canonical');
$this->registerMetaTag(['property' => 'og:site_name', 'content' => Yii::$app->name], 'og:site_name');
$this->registerMetaTag(['property' => 'og:type', 'content' => $ogType], 'og:type');
$this->registerMetaTag(['property' => 'og:title', 'content' => $socialTitle], 'og:title');
$this->registerMetaTag(['property' => 'og:description', 'content' => $socialDescription], 'og:description');
$this->registerMetaTag(['property' => 'og:url', 'content' => $canonicalUrl], 'og:url');
$this->registerMetaTag(['name' => 'twitter:card', 'content' => $socialImage !== '' ? 'summary_large_image' : 'summary'], 'twitter:card');
$this->registerMetaTag(['name' => 'twitter:title', 'content' => $socialTitle], 'twitter:title');
$this->registerMetaTag(['name' => 'twitter:description', 'content' => $socialDescription], 'twitter:description');

if ($socialImage !== '') {
    $this->registerMetaTag(['property' => 'og:image', 'content' => $socialImage], 'og:image');
    $this->registerMetaTag(['name' => 'twitter:image', 'content' => $socialImage], 'twitter:image');
}
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96"/>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg"/>
        <link rel="shortcut icon" href="/favicon.ico"/>
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
        <meta name="apple-mobile-web-app-title" content="BrickAtlas"/>
        <link rel="manifest" href="/site.webmanifest"/>

        <?= JsonLdRenderer::render($schemaGraph) ?>
        <?php $this->head() ?>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header class="text-bg-dark">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <?php NavBar::begin([
                    'id'         => 'menu-navbar',
                    'brandLabel' => Html::img('@web/images/logo-transparent.png', [
                                    'alt'     => Yii::$app->name,
                                    'loading' => 'lazy',
                                    'style'   => 'height: 32px; width: 32px;',
                                    'class'   => 'd-inline-block align-text-top',
                            ]) . ' ' . Html::encode(Yii::$app->name),
                    'brandUrl'   => Yii::$app->homeUrl,
                    'options'    => [
                            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
                    ],
            ]);
            $menuItems = [
                //['label' => 'LEGO<sup>®</sup>', 'url' => ['/lego']],
            ];

            echo Nav::widget([
                    'options'      => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
                    'encodeLabels' => false,
                    'items'        => $menuItems,
            ]);

            if (!Yii::$app->user->isGuest) {
                echo $this->render('_user-dropdown-menu', ['user' => Yii::$app->user->identity]);
            }

            NavBar::end(); ?>
        </div>
    </header>

    <main role="main" class="flex-shrink-0">

        <div id="presentation"></div>

        <div class="container">
            <?= Breadcrumbs::widget([
                    'links'        => $breadcrumbLinks,
                    'homeLink'     => $homeBreadcrumb,
                    'encodeLabels' => false,
                    'options'      => ['class' => 'breadcrumb p-3 bg-body-tertiary rounded-3'],
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="footer mt-auto py-3 text-muted">
        <div class="container">
            <p class="float-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            <p class="float-end mb-0 text-end">
                <?= T::tr('Some product links are affiliate links, which means we may earn a commission if you make a purchase through our website.') ?>
                <br>
                <?= T::tr('LEGO® is a trademark of the LEGO Group. This website is not sponsored, authorized, or endorsed by the LEGO Group.') ?>
            </p>
        </div>
    </footer>

    <div class="modal fade" id="mainModal" tabindex="-1" aria-hidden="true"></div>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage();