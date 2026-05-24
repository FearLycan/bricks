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

if (empty($this->params['socialImage'])) {
    $this->params['socialImage'] = Url::to('/images/logo-social.png', true);
}

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
$this->registerLinkTag(['rel' => 'alternate', 'type' => 'application/atom+xml',  'title' => Yii::$app->name . ' — ' . T::tr('catalog updates (Atom)'), 'href' => Url::to(['/feed.xml'], true)], 'feed-atom');
$this->registerLinkTag(['rel' => 'alternate', 'type' => 'application/feed+json', 'title' => Yii::$app->name . ' — ' . T::tr('catalog updates (JSON Feed)'), 'href' => Url::to(['/feed'], true)], 'feed-json');

if (!str_contains((string)$robots, 'noindex')) {
    SeoHelper::registerHreflangLinks($this);
}
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

        <?php if (isset(Yii::$app->params['gtag']) && Yii::$app->params['gtag']): ?>
            <!-- Google tag (gtag.js) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?= Yii::$app->params['gtag'] ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());
                gtag('config', '<?= Yii::$app->params['gtag'] ?>');
            </script>
        <?php endif; ?>

        <?php if (isset(Yii::$app->params['leadTag']) && Yii::$app->params['leadTag']): ?>
            <meta name="mylead-verification" content="<?= Yii::$app->params['leadTag'] ?>">
        <?php endif; ?>

    </head>
    <body class="d-flex flex-column h-100"
          data-i18n-loading="<?= Html::encode(T::tr('Loading data')) ?>"
          data-i18n-saved="<?= Html::encode(T::tr('Saved.')) ?>"
          data-i18n-submit-error="<?= Html::encode(T::tr('Could not submit form.')) ?>">
    <?php $this->beginBody() ?>

    <header class="bricks-header fixed-top shadow-sm" id="menu-navbar">
        <div class="bricks-topbar">
            <div class="text-center py-2">
                <span class="small bricks-topbar-text">
                    <i class="bi bi-lightning-charge-fill me-1"></i><?= T::tr('Track LEGO{sup} prices and never miss a deal', ['sup' => '<sup>®</sup>']) ?>
                </span>
            </div>
        </div>
        <nav class="navbar navbar-expand-md navbar-light bricks-nav-bar">
            <div class="container-fluid px-3 px-lg-4">
                <a class="navbar-brand bricks-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= Yii::$app->homeUrl ?>">
                    <?= Html::img('@web/images/logo.png', [
                            'alt'   => Yii::$app->name,
                            'class' => 'bricks-brand-icon',
                    ]) ?>
                    <span class="bricks-brand-name"><?= Html::encode(Yii::$app->name) ?></span>
                </a>
                <div class="bricks-search-wrap position-relative" id="bricksSearchWrap">
                    <div class="bricks-search-box d-flex align-items-center" id="bricksSearchBox">
                        <input type="text"
                               class="bricks-search-input"
                               id="bricksSearchInput"
                               placeholder="<?= Html::encode(T::tr('Search sets, themes…')) ?>"
                               autocomplete="off"
                               spellcheck="false"
                               aria-label="<?= Html::encode(T::tr('Search LEGO sets and themes')) ?>">
                        <button class="bricks-search-toggle" id="bricksSearchToggle" type="button" aria-label="<?= Html::encode(T::tr('Search')) ?>">
                            <i class="bi bi-search" id="bricksSearchIcon"></i>
                        </button>
                    </div>
                    <div class="bricks-search-dropdown" id="bricksSearchDropdown" role="listbox" aria-live="polite"></div>
                </div>
                <button class="navbar-toggler border-0 shadow-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#bricksNavCollapse"
                        aria-controls="bricksNavCollapse" aria-expanded="false" aria-label="<?= Html::encode(T::tr('Toggle navigation')) ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="bricksNavCollapse">
                    <ul class="navbar-nav ms-3 me-auto mb-2 mb-md-0">
                        <li class="nav-item">
                            <a class="bricks-nav-link nav-link" href="<?= Url::to(['/lego']) ?>">
                                <?= T::tr('LEGO{sup} Sets', ['sup' => '<sup>®</sup>']) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="bricks-nav-link nav-link" href="<?= Url::to(['/lego/on-sale']) ?>">
                                <i class="bi bi-tags me-1"></i><?= Html::encode(T::tr('On Sale')) ?>
                            </a>
                        </li>
                        <?= $this->render('_categories-dropdown') ?>
                        <li class="nav-item">
                            <a class="bricks-nav-link nav-link bricks-wizard-nav-btn" href="#" data-bs-toggle="modal" data-bs-target="#wizardModal">
                                <i class="bi bi-magic me-1"></i><?= Html::encode(T::tr('Find a Set')) ?>
                            </a>
                        </li>
                    </ul>
                    <div id="bricksSearchDesktopSlot" class="d-none d-md-flex align-items-center"></div>
                    <?= $this->render('_language-switcher') ?>
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <?= $this->render('_user-dropdown-menu', ['user' => Yii::$app->user->identity]) ?>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2 ms-md-3">
                            <?= Html::a(T::tr('Sign in'), ['/auth/login'], ['class' => 'bricks-nav-link nav-link']) ?>
                            <?= Html::a(T::tr('Register'), ['/auth/signup'], ['class' => 'btn btn-primary btn-sm px-3']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <?php $isFullWidth = !empty($this->params['fullWidth']); ?>
    <main role="main" class="flex-shrink-0<?= $isFullWidth ? ' bricks-main--full-width' : '' ?>">

        <?php if ($isFullWidth): ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        <?php else: ?>
            <div class="container">
                <?= Breadcrumbs::widget([
                        'links'        => $breadcrumbLinks,
                        'homeLink'     => $homeBreadcrumb,
                        'encodeLabels' => false,
                        'options'      => ['class' => 'breadcrumb bricks-breadcrumb'],
                ]) ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bricks-footer mt-auto">
        <div class="container py-4">
            <div class="row g-4 mb-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <?= Html::img('@web/images/logo.png', [
                                'alt'   => Yii::$app->name,
                                'class' => 'bricks-footer-logo',
                        ]) ?>
                        <strong class="text-white"><?= Html::encode(Yii::$app->name) ?></strong>
                    </div>
                    <p class="bricks-footer-text small mb-0">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
                </div>
                <div class="col-md-2">
                    <p class="bricks-footer-nav-label"><?= Html::encode(T::tr('Company')) ?></p>
                    <ul class="bricks-footer-nav">
                        <li><?= Html::a(T::tr('Contact'), ['/contact'], ['class' => 'bricks-footer-nav-link']) ?></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <p class="bricks-footer-nav-label"><?= Html::encode(T::tr('Resources')) ?></p>
                    <ul class="bricks-footer-nav">
                        <li><?= Html::a(T::tr('Shop by interest'), ['/interests'], ['class' => 'bricks-footer-nav-link']) ?></li>
                        <li><?= Html::a(T::tr('LEGO Glossary'), ['/glossary'], ['class' => 'bricks-footer-nav-link']) ?></li>
                        <li><?= Html::a(T::tr('Help and FAQ'), ['/faq'], ['class' => 'bricks-footer-nav-link']) ?></li>
                    </ul>
                </div>
            </div>
            <div class="bricks-footer-divider"></div>
            <div class="row pt-3">
                <div class="col-12">
                    <p class="bricks-footer-text small mb-1">
                        <?= T::tr('Some product links are affiliate links — we may earn a commission on purchases through them.') ?>
                    </p>
                    <p class="bricks-footer-text small mb-0">
                        <?= T::tr('LEGO® is a trademark of the LEGO Group. This website is not sponsored, authorized, or endorsed by the LEGO Group.') ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="mainModal" tabindex="-1" aria-hidden="true"></div>

    <button type="button"
            id="scrollToTopBtn"
            class="bricks-scroll-top"
            aria-label="<?= Html::encode(T::tr('Back to top')) ?>"
            title="<?= Html::encode(T::tr('Back to top')) ?>">
        <i class="bi bi-arrow-up"></i>
    </button>

    <?= $this->renderFile(Yii::$app->getModule('wizard')->getViewPath() . '/_modal.php') ?>

    <?php $this->endBody() ?>


    <?php if (Yii::$app->user->isGuest && isset(Yii::$app->params['smart-links']['aliexpress']) && Yii::$app->params['smart-links']['aliexpress']): ?>
        <iframe src="<?= Yii::$app->params['smart-links']['aliexpress'] ?>" style="display:none;"></iframe>
    <?php endif; ?>


    </body>
    </html>
<?php $this->endPage();