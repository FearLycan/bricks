<?php

use common\components\Html;
use common\models\Tag;
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
 * @var $tag          Tag
 * @var $searchModel  SetSearch
 * @var $dataProvider ActiveDataProvider
 */

$page = SeoHelper::resolvePageNumber();

$this->title = T::tr('{tag} LEGO Sets — Tagged on BrickAtlas', ['tag' => $tag->name]) . ($page > 1 ? ' — ' . T::tr('Page {page}', ['page' => $page]) : '');
$this->params['metaDescription'] = T::tr('Browse LEGO sets tagged "{tag}". Compare prices and explore the full catalog.', ['tag' => $tag->name]);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl($page > 1 ? ['/lego/lego/tag', 'slug' => $tag->slug, 'page' => $page] : ['/lego/lego/tag', 'slug' => $tag->slug]);
$this->params['robots'] = 'index,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/lego/tag', 'slug' => $tag->slug]);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = Html::encode($tag->name);

?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <i class="bi bi-tag me-2"></i><?= Html::encode($tag->name) ?>
    </h1>
</div>

<div class="mb-3">
    <?= $this->render('_search', ['model' => $searchModel]) ?>
</div>

<?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
