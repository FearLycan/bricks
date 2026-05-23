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

$this->title = SeoHelper::buildMagazinesTitle($page);
$this->params['metaDescription'] = SeoHelper::buildMagazinesDescription($page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(
    $page > 1 ? ['/lego/magazines', 'page' => $page] : ['/lego/magazines']
);
$this->params['robots'] = 'index,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/magazines']);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('Magazines');

$this->params['fullWidth'] = true;
?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Categories'),
        'title'           => T::tr('LEGO Magazine Sets'),
        'intro'           => SeoHelper::buildMagazinesIntro(),
        'image'           => 'images/categories/magazines.jpg',
        'icon'            => 'bi-newspaper',
        'modifier'        => 'magazines',
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <div class="mb-3">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>

    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
</div>
