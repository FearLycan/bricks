<?php

use common\components\Html;
use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\SeoHelper;
use frontend\components\T;
use frontend\models\searches\SetSearch;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $searchModel  SetSearch
 * @var $dataProvider ActiveDataProvider
 */

$page = max(1, (int)Yii::$app->request->get('new_page', 1));

$this->title = SeoHelper::buildNewArrivalsTitle($page);
$this->params['metaDescription'] = SeoHelper::buildNewArrivalsDescription($page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl($page > 1 ? ['/lego/new', 'new_page' => $page] : ['/lego/new']
);
$this->params['robots'] = 'index,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/new'], 'new_page');

$this->params['breadcrumbs'][] = ['label' => T::tr('LEGO Sets'), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('New Arrivals');

?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <i class="bi bi-stars me-2 text-warning"></i><?= Html::encode($this->title) ?>
    </h1>
    <p class="text-body-secondary mb-3">
        <?= T::tr("Recently added LEGO sets, the newest first. Check back often to discover what's new in the catalog.") ?>
    </p>
</div>

<?= $this->render('_search-new', ['model' => $searchModel]) ?>

<?= $this->render('_list-new', ['dataProvider' => $dataProvider]) ?>
