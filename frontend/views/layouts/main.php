<?php

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\web\View;

/**
 * @var View   $this
 * @var string $content
 */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="dark">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="archive post-type-archive post-type-archive-product theme-bootscore woocommerce-shop woocommerce woocommerce-page woocommerce-js hfeed">
    <?php $this->beginBody() ?>

    <div id="page" class="site">
        <header id="masthead" class="sticky-top bg-body-tertiary site-header">
            <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->name,
                'brandImage' => '/bootscore/img/logo/logo-theme-dark.svg',
                'brandUrl'   => Yii::$app->homeUrl,
                'options'    => [
                    'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
                ],
            ]);
            $menuItems = [
                ['label' => 'Home', 'url' => ['/site/index']],
                ['label' => 'About', 'url' => ['/site/about']],
                ['label' => 'Contact', 'url' => ['/site/contact']],
            ];
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
            }

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
                'items'   => $menuItems,
            ]);
            if (Yii::$app->user->isGuest) {
                echo Html::tag('div', Html::a('Login', ['/site/login'], ['class' => ['btn btn-link login text-decoration-none']]), ['class' => ['d-flex']]);
            } else {
                echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'btn btn-link logout text-decoration-none']
                    )
                    . Html::endForm();
            }
            NavBar::end();
            ?>
        </header>

        <div id="content" class="site-content container pt-3 pb-5">
            <div id="primary" class="content-area">
                <main role="main" class="site-main">
                    <div class="row">
                        <?= Breadcrumbs::widget([
                            'links' => $this->params['breadcrumbs'] ?? [],
                        ]) ?>
                        <?= Alert::widget() ?>
                        <?= $content ?>
                    </div>
                </main>
            </div>
        </div>

        <footer class="bootscore-footer">
            <div class="bg-body-tertiary border-bottom py-5 bootscore-footer-top">
                <div class="container">
                    <div class="widget footer_widget">
                        <div class="row">
                            <div class="col-md-11">
                                <p>
                                    <noscript><img decoding="async" src="https://bootscore.me/wp-content/themes/bootscore-custom-child/assets/img/logo/logo.svg" alt="Bootscore Logo" class="d-td-none me-2" width="60"></noscript>
                                    <img decoding="async" src="data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2060%2040%22%3E%3C/svg%3E" data-src="https://bootscore.me/wp-content/themes/bootscore-custom-child/assets/img/logo/logo.svg" alt="Bootscore Logo" class="lazyload d-td-none me-2" width="60">
                                    <noscript><img decoding="async" src="https://bootscore.me/wp-content/themes/bootscore-custom-child/assets/img/logo/logo-theme-dark.svg" alt="Bootscore Logo" class="d-tl-none me-2" width="60"></noscript>
                                    <img decoding="async" src="https://bootscore.me/wp-content/themes/bootscore-custom-child/assets/img/logo/logo-theme-dark.svg" data-src="https://bootscore.me/wp-content/themes/bootscore-custom-child/assets/img/logo/logo-theme-dark.svg" alt="Bootscore Logo" class="d-tl-none me-2 ls-is-cached lazyloaded" width="60"></p>
                                <p class="mb-md-0">Built and maintained with <i class="fa-solid fa-heart"></i> by a few <a target="_blank" href="https://github.com/bootscore/bootscore/graphs/contributors">people</a>.</p></div>
                            <div class="col-md-1">
                                <div class="h-100 d-flex justify-content-md-end align-items-center align-items-md-end"><a class="fs-5 me-2" href="https://github.com/bootscore" target="_blank" title="bootScore @GitHub"><i class="fab fa-github"></i></a> <a class="fs-5 me-2" href="https://twitter.com/_bootscore" target="_blank" title="bootScore @Twitter"><i class="fa-brands fa-twitter"></i></a> <a class="fs-5 text-decoration-none" style="margin-bottom: -2px;" href="https://opencollective.com/bootscore" target="_blank" title="bootScore @OpenCollective"><span class="icon-open-collective"></span></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-body-tertiary pt-5 pb-4 bootscore-footer-columns">
                <div class="container">
                    <div class="row">
                        <div class="col-6 col-lg-3">
                            <div class="widget_text widget footer_widget mb-3"><h2 class="widget-title h5">Privacy</h2>
                                <div class="textwidget custom-html-widget">
                                    <ul class="list-unstyled mb-0">
                                        <li><a data-bs-toggle="modal" href="#bs-cookie-modal" aria-haspopup="dialog">Cookie Preferences</a></li>
                                        <li><a href="https://bootscore.me/privacy-policy/">Privacy Policy</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="widget footer_widget mb-3"><h2 class="widget-title h5">Support</h2>
                                <div class="menu-support-container">
                                    <ul id="menu-support" class="menu">
                                        <li id="menu-item-4201" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4201"><a href="https://bootscore.me/support/">Purchased Products</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="widget footer_widget mb-3"><h2 class="widget-title h5">Legal</h2>
                                <div class="menu-legal-container">
                                    <ul id="menu-legal" class="menu">
                                        <li id="menu-item-283" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-283"><a href="https://bootscore.me/imprint/">Imprint</a></li>
                                        <li id="menu-item-22828" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-22828"><a target="_blank" rel="noopener" href="https://github.com/orgs/bootscore/discussions">Contact</a></li>
                                        <li id="menu-item-207" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-207"><a href="https://bootscore.me/terms/">Terms &amp; Conditions</a></li>
                                        <li id="menu-item-2494" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2494"><a href="https://bootscore.me/revocation/">Revocation Policy</a></li>
                                        <li id="menu-item-672" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-672"><a href="https://bootscore.me/license-credits/">License &amp; Credits</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="widget footer_widget mb-3"><h2 class="widget-title h5">Community</h2>
                                <div class="menu-community-container">
                                    <ul id="menu-community" class="menu">
                                        <li id="menu-item-21413" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-21413"><a target="_blank" rel="noopener" href="https://github.com/orgs/bootscore/discussions">Discussions</a></li>
                                        <li id="menu-item-21414" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-21414"><a target="_blank" rel="noopener" href="https://github.com/bootscore/bootscore/issues">Issues</a></li>
                                        <li id="menu-item-22812" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-22812"><a target="_blank" rel="noopener" href="https://github.com/sponsors/bootscore">GitHub Sponsors</a></li>
                                        <li id="menu-item-22811" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-22811"><a target="_blank" rel="noopener" href="https://opencollective.com/bootscore">Open Collective</a></li>
                                        <li id="menu-item-22837" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-22837"><a href="https://bootscore.me/private-info-form/">Private Info Form</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="footer_widget mb-3">
                                <div class="wp-block-group is-layout-constrained wp-block-group-is-layout-constrained"><h2 class="wp-block-heading widget-title h5">Payments</h2>
                                    <p class="text-secondary display-4"><i class="fab fa-cc-paypal"></i> <i class="fab fa-cc-stripe"></i></p></div>
                            </div>
                        </div>
                    </div>
                    <ul id="footer-menu" class="nav ">
                        <li id="menu-item-21415" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home nav-item nav-item-21415"><a href="https://bootscore.me/" class="nav-link ">Home</a></li>
                        <li id="menu-item-3817" class="menu-item menu-item-type-post_type menu-item-object-page nav-item nav-item-3817"><a href="https://bootscore.me/theme/" class="nav-link ">Theme</a></li>
                        <li id="menu-item-3818" class="menu-item menu-item-type-post_type menu-item-object-page nav-item nav-item-3818"><a href="https://bootscore.me/plugins/" class="nav-link ">Plugins</a></li>
                        <li id="menu-item-3819" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item current_page_item nav-item nav-item-3819"><a href="https://bootscore.me/shop/" class="nav-link active">Shop</a></li>
                        <li id="menu-item-3820" class="menu-item menu-item-type-taxonomy menu-item-object-category nav-item nav-item-3820"><a href="https://bootscore.me/documentation/" class="nav-link ">Docs</a></li>
                        <li id="menu-item-3821" class="menu-item menu-item-type-taxonomy menu-item-object-category nav-item nav-item-3821"><a href="https://bootscore.me/blog/" class="nav-link ">Blog</a></li>
                    </ul>
                </div>
            </div>
            <div class="bg-body-tertiary text-body-secondary border-top py-2 text-center bootscore-footer-info">
                <div class="container">
                    <div class="small bootscore-copyright">
                        <span class="cr-symbol">©</span><?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?>
                    </div>
                </div>
            </div>
        </footer>

        <a href="#" class="btn btn-primary shadow position-fixed zi-1000 top-button">
            <i class="fa-solid fa-chevron-up"></i>
            <span class="visually-hidden-focusable">To top</span>
        </a>
    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage();
