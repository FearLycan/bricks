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
 * @var $slug         string
 * @var $config       array
 * @var $searchModel  SetSearch
 * @var $dataProvider ActiveDataProvider
 */

$page = SeoHelper::resolvePageNumber();

$this->title = SeoHelper::buildAudienceTitle($slug, $page);
$this->params['metaDescription'] = SeoHelper::buildAudienceDescription($slug, $page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(
    $page > 1 ? ['/lego/' . $slug, 'page' => $page] : ['/lego/' . $slug]
);
$this->params['robots'] = 'index,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/' . $slug]);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = $config['breadcrumb'];

$this->params['fullWidth'] = true;
?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Categories'),
        'title'           => $config['heroTitle'],
        'intro'           => $config['intro'],
        'image'           => $config['image'],
        'icon'            => $config['icon'],
        'modifier'        => $config['modifier'],
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <div class="mb-3">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>

    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
</div>
