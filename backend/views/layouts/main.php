<?php

/** @var \yii\web\View $this */

/** @var string $content */

use backend\assets\AppAsset;
use common\widgets\Alert;
use frontend\components\T;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="backend-layout d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <main role="main" class="backend-main flex-grow-1">
        <div class="container-fluid">
            <div class="row min-vh-100">
                <div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-bg-dark col-md-3 col-lg-2">
                    <a href="/" class="sidebar-brand d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <i class="bi bi-boxes me-2"></i>
                        <span class="fs-5">BrickAtlas Admin</span>
                    </a>
                    <hr>

                    <?= Nav::widget([
                            'options'      => ['class' => 'nav nav-pills flex-column mb-auto'],
                            'encodeLabels' => false,
                            'items'        => [
                                    [
                                            'label'       => '<i class="bi bi-speedometer2"></i> ' . T::tr('Dashboard'),
                                            'linkOptions' => ['class' => 'nav-link text-white'],
                                            'url'         => ['/admin/dashboard/index'],
                                    ],
                                    [
                                            'label'       => '<i class="bi bi-list-ul"></i> ' . T::tr('Set'),
                                            'linkOptions' => ['class' => 'nav-link text-white'],
                                            'url'         => ['/admin/set/index'],
                                    ],
                            ],
                    ]) ?>

                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://placehold.co/64" alt="" width="32" height="32" class="rounded-circle me-2"> <strong><?= Yii::$app->user->identity->username ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="#">New project...</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" data-method="post" href="/site/logout">Sign out</a></li>
                        </ul>
                    </div>
                </div>

                <div class="backend-panel col-md-9 ms-sm-auto col-lg-10 px-md-3 pt-2 pb-2">
                    <header class="d-none">
                        <?php
                        NavBar::begin([
                                'brandLabel' => Yii::$app->name,
                                'brandUrl'   => Yii::$app->homeUrl,
                                'options'    => [
                                        'class' => 'navbar navbar-expand-md navbar-dark bg-dark',
                                ],
                        ]);
                        $menuItems = [
                                ['label' => 'Home', 'url' => ['/site/index']],
                        ];
                        if (Yii::$app->user->isGuest) {
                            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
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
                    <div class="backend-content">
                        <?= Breadcrumbs::widget([
                                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        ]) ?>
                        <?= Alert::widget() ?>
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage();
