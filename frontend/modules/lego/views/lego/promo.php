<?php

use common\components\Html;
use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\SeoHelper;
use frontend\components\T;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 */

$page = max(1, (int)Yii::$app->request->get('promo_page', 1));

$this->title = SeoHelper::buildPromoTitle($page);
$this->params['metaDescription'] = SeoHelper::buildPromoDescription($page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(
    $page > 1 ? ['/lego/on-sale', 'promo_page' => $page] : ['/lego/on-sale']
);
// An empty "best deals" page is thin content: keep it out of the index
// until there is at least one genuine offer, but let crawlers follow links.
$this->params['robots'] = $dataProvider->getTotalCount() > 0 ? 'index,follow' : 'noindex,follow';

SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego/on-sale'], 'promo_page');

$this->params['breadcrumbs'][] = ['label' => T::tr('LEGO Sets'), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('On Sale');

?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <i class="bi bi-tags me-2 text-danger"></i><?= Html::encode($this->title) ?>
    </h1>
    <p class="text-body-secondary mb-3">
        <?= Html::encode(SeoHelper::buildCatalogIntro()) ?>
    </p>
</div>

<?php if ($dataProvider->getTotalCount() > 0): ?>
    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
<?php else: ?>
    <div class="col-lg-12">
        <div class="p-4 p-md-5 mb-4 rounded-3 border bg-body-tertiary text-center">
            <i class="bi bi-hourglass-split fs-1 text-danger d-block mb-3" aria-hidden="true"></i>
            <h2 class="h4 mb-2"><?= Html::encode(T::tr('No deals right now')) ?></h2>
            <p class="text-body-secondary mb-4 mx-auto" style="max-width: 36rem;">
                <?= Html::encode(T::tr('We are adding offers from more stores, including Polish and European retailers. In the meantime, take a look at:')) ?>
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?= Html::a('<i class="bi bi-stars me-1"></i>' . Html::encode(T::tr('New arrivals')), ['/lego/new'], ['class' => 'btn btn-danger']) ?>
                <?= Html::a('<i class="bi bi-clock-history me-1"></i>' . Html::encode(T::tr('Sets retiring soon')), ['/lego/retiring-soon'], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('<i class="bi bi-grid me-1"></i>' . Html::encode(T::tr('Browse all sets')), ['/lego'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
<?php endif; ?>
