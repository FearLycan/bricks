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
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header class="text-bg-dark">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <?php NavBar::begin([
                    'brandLabel' => Html::img('@web/images/logo-theme-dark.svg', [
                                    'alt'   => Yii::$app->name,
                                    'class' => 'd-inline-block align-text-top',
                            ]) . ' ' . Html::encode(Yii::$app->name),
                    'brandUrl'   => Yii::$app->homeUrl,
                    'options'    => [
                            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
                    ],
            ]);
            $menuItems = [
                    ['label' => 'LEGO<sup>®</sup>', 'url' => ['/lego']],
            ];

            echo Nav::widget([
                    'options'      => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
                    'encodeLabels' => false,
                    'items'        => $menuItems,
            ]);
            NavBar::end(); ?>
        </div>
    </header>

    <main role="main" class="flex-shrink-0">
        <div class="container">
            <?= Breadcrumbs::widget([
                    'links'        => $this->params['breadcrumbs'] ?? [],
                    'homeLink'     => [
                            'label' => Html::encode(Yii::$app->name),
                            'url'   => Yii::$app->homeUrl,
                    ],
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
            <p class="float-end"></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage();