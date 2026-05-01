<?php

use common\components\Html;
use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\SeoHelper;
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
$this->params['robots'] = 'index,follow';

$this->params['breadcrumbs'][] = ['label' => 'LEGO Sets', 'url' => ['/lego']];
$this->params['breadcrumbs'][] = 'On Sale';

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

<?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
