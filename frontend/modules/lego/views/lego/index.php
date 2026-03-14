<?php

use common\components\Html;
use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\SeoHelper;
use frontend\models\searches\SetSearch;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 * @var $searchModel  SetSearch
 */

$page = SeoHelper::resolvePageNumber();
$hasActiveFilters = SeoHelper::hasActiveCatalogFilters(Yii::$app->request->queryParams);

$this->title = $hasActiveFilters
        ? SeoHelper::buildFilteredCatalogTitle()
        : SeoHelper::buildCatalogTitle($page);
$this->params['metaDescription'] = $hasActiveFilters
        ? SeoHelper::buildFilteredCatalogDescription()
        : SeoHelper::buildCatalogDescription($page);
$this->params['canonicalUrl'] = $hasActiveFilters
        ? SeoHelper::buildAbsoluteUrl(['/lego'])
        : SeoHelper::buildAbsoluteUrl($page > 1 ? ['/lego', 'page' => $page] : ['/lego']);
$this->params['robots'] = $hasActiveFilters ? 'noindex,follow' : 'index,follow';

?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <?= Html::encode($this->title) ?>
    </h1>
    <p class="text-body-secondary mb-3">
        <?= Html::encode(SeoHelper::buildCatalogIntro()) ?>
    </p>
</div>

<?= $this->render('_search', ['model' => $searchModel]) ?>

<?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>



