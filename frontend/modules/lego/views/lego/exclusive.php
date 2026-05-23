<?php

use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\Helper;
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

$page = SeoHelper::resolvePageNumber();

$this->title = SeoHelper::buildExclusiveTitle($page);
$this->params['metaDescription'] = SeoHelper::buildExclusiveDescription($page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(
    $page > 1 ? ['/lego/exclusive', 'page' => $page] : ['/lego/exclusive']
);
$this->params['robots'] = 'index,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/exclusive']);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('Exclusive');

$this->params['fullWidth'] = true;
?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Categories'),
        'title'           => T::tr('LEGO Exclusive Sets'),
        'intro'           => SeoHelper::buildExclusiveIntro(),
        'image'           => 'images/categories/exclusive.jpg',
        'icon'            => 'bi-gem',
        'modifier'        => 'exclusive',
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <div class="mb-3">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>

    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
</div>
