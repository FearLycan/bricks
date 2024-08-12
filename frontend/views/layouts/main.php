<?php

use common\widgets\Alert;
use frontend\assets\AppAsset;
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

    <?php
    $menuItems = [
        [
            'label'       => 'Home',
            'url'         => Yii::$app->homeUrl,
            'options'     => ['class' => 'menu-item menu-item-type-post_type menu-item-object-page current_page_parent nav-item nav-item-731'],
            'linkOptions' => ['class' => 'nav-link'],
        ],
        //['label' => 'Products', 'url' => ['/products']],
        ['label' => 'LEGO<sup>®</sup>', 'url' => ['/lego']],
    ];
    ?>

    <div id="page" class="site">
        <header id="masthead" class="sticky-top bg-body-tertiary site-header">
            <nav id="nav-main" class="navbar navbar-expand-lg">
                <div class="container">
                    <a class="navbar-brand" href="<?= Yii::$app->homeUrl ?>">
                        <img src="<?= Url::to('/bootscore/img/logo/logo-theme-dark.svg') ?>" alt="Logo <?= Yii::$app->name ?>" loading="lazy" class="d-tl-none me-2">
                    </a>
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-navbar">
                        <div class="offcanvas-header"><span class="h5 offcanvas-title">Menu</span>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <?= Nav::widget([
                                'options'      => ['class' => 'navbar-nav ms-auto'],
                                'items'        => $menuItems,
                                'encodeLabels' => false,
                            ]) ?>
                        </div>
                    </div>
                    <div class="header-actions d-flex align-items-center">
                        <button class="btn btn-outline-secondary ms-1 ms-md-2 search-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-search" aria-expanded="false" aria-controls="collapse-search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span class="visually-hidden-focusable">Search</span>
                        </button>
                        <button class="btn btn-outline-secondary d-lg-none ms-1 ms-md-2 nav-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-navbar" aria-controls="offcanvas-navbar">
                            <i class="fa-solid fa-bars"></i>
                            <span class="visually-hidden-focusable">Menu</span>
                        </button>
                    </div>
                </div>
            </nav>

            <div class="bg-body-tertiary position-absolute start-0 end-0 collapse" id="collapse-search" style="">
                <div class="container pb-2">
                    <div class="widget top-nav-search">
                        <form novalidate="novalidate" role="search" method="get" action="/" class="wp-block-search__button-inside wp-block-search__icon-button wp-block-search"><label class="wp-block-search__label screen-reader-text" for="wp-block-search__input-1">Search</label>
                            <div class="wp-block-search input-group "><input class="wp-block-search__input form-control" id="wp-block-search__input-1" placeholder="Search products…" value="" type="search" name="s" required=""><input type="hidden" name="post_type" value="product">
                                <button aria-label="Search" class="wp-block-search__button btn btn-outline-secondary has-icon wp-element-button" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </header>

        <div id="content" class="site-content container pt-3 pb-5">
            <div id="primary" class="content-area">
                <main role="main" class="site-main">

                    <?= Breadcrumbs::widget([
                        'homeLink'     => ['label' => '', 'url' => Yii::$app->homeUrl,],
                        'options'      => ['class' => 'breadcrumb flex-nowrap mb-0'],
                        'navOptions'   => ['aria' => ['label' => 'breadcrumb'], 'class' => 'wc-breadcrumb overflow-x-auto text-nowrap mb-4 mt-2 py-2 px-3 bg-body-tertiary rounded'],
                        'links'        => $this->params['breadcrumbs'] ?? [],
                        'encodeLabels' => false,
                    ]) ?>

                    <div class="row">
                        <?= Alert::widget() ?>
                        <?= $content ?>
                    </div>
                </main>
            </div>
        </div>

        <footer class="bootscore-footer">
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
