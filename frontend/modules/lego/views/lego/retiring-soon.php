<?php

use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\Helper;
use frontend\components\SeoHelper;
use frontend\components\T;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 */

$page = SeoHelper::resolvePageNumber();

$this->title = SeoHelper::buildRetiringSoonTitle($page);
$this->params['metaDescription'] = SeoHelper::buildRetiringSoonDescription($page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(
    $page > 1 ? ['/lego/retiring-soon', 'page' => $page] : ['/lego/retiring-soon']
);
$this->params['robots'] = 'index,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/retiring-soon']);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('Retiring Soon');

$this->params['fullWidth'] = true;
?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Categories'),
        'title'           => T::tr('LEGO Sets Retiring Soon'),
        'intro'           => SeoHelper::buildRetiringSoonIntro(),
        'image'           => 'images/categories/retiring-soon.jpg',
        'icon'            => 'bi-hourglass-split',
        'modifier'        => 'retiring-soon',
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
</div>
