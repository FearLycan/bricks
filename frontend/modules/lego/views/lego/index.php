<?php

use common\components\Html;
use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\SeoHelper;
use frontend\models\searches\SetSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 * @var $searchModel  SetSearch
 * @var $wizardData   array|null
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

if (!$hasActiveFilters) {
    SeoHelper::registerPaginationLinks($this, $dataProvider, $page, ['/lego']);
}

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

<?php if (!empty($wizardData)): ?>
    <div class="alert wizard-banner mb-3 d-flex align-items-start gap-3">
        <i class="bi bi-magic wizard-banner-icon flex-shrink-0"></i>
        <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold wizard-banner-title">Results matched by the finder</div>
            <div class="wizard-banner-tags mt-1">
                <?php foreach ($wizardData['labels'] as $label): ?>
                    <span class="wizard-banner-tag"><?= Html::encode($label) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="button" class="btn btn-sm wizard-banner-edit flex-shrink-0"
                data-wizard-answers="<?= Html::encode(Json::encode($wizardData['answers'])) ?>"
                title="Edit finder selections">
            <i class="bi bi-pencil"></i>
        </button>
        <a href="<?= Html::encode(Url::to(['/lego'])) ?>" class="btn btn-sm wizard-banner-clear flex-shrink-0" title="Clear finder filters">
            <i class="bi bi-x-lg"></i>
        </a>
    </div>
<?php endif; ?>

<div class="mb-3">
    <?= $this->render('_search', ['model' => $searchModel]) ?>
</div>

<?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
