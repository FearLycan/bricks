<?php

use common\components\Html;
use common\models\Theme;
use common\schema\factory\ItemListSchemaFactory;
use common\schema\JsonLdRenderer;
use frontend\components\Helper;
use frontend\components\SeoHelper;
use frontend\models\searches\SetSearch;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $theme        Theme
 * @var $subTheme     Theme
 * @var $dataProvider ActiveDataProvider
 * @var $searchModel  SetSearch
 */

$page = SeoHelper::resolvePageNumber();

$this->title = SeoHelper::buildThemeTitle($theme, $subTheme, $page);
$this->params['metaDescription'] = SeoHelper::buildThemeDescription($theme, $subTheme, $page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl($subTheme
        ? ($page > 1
                ? ['/lego/theme/index', 'slug' => $theme->slug, 'sub' => $subTheme->slug, 'page' => $page]
                : ['/lego/theme/index', 'slug' => $theme->slug, 'sub' => $subTheme->slug])
        : ($page > 1
                ? ['/lego/theme/index', 'slug' => $theme->slug, 'page' => $page]
                : ['/lego/theme/index', 'slug' => $theme->slug])
);
$this->params['robots'] = 'index,follow';

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];

if ($subTheme) {
    $this->params['breadcrumbs'][] = ['label' => $theme->name, 'url' => ['/lego/theme/' . $theme->slug]];
    $this->params['breadcrumbs'][] = SeoHelper::normalizeText($subTheme->name);
    if ($subTheme->img) {
        $this->params['socialImage'] = SeoHelper::buildAbsoluteUrl($subTheme->img);
    }
} else {
    $this->params['breadcrumbs'][] = SeoHelper::normalizeText($theme->name);
    if ($theme->img) {
        $this->params['socialImage'] = SeoHelper::buildAbsoluteUrl($theme->img);
    }
}

?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <?= Html::encode($this->title) ?>
    </h1>
    <p class="text-body-secondary mb-3">
        <?= Html::encode(SeoHelper::buildThemeIntro($theme, $subTheme)) ?>
    </p>
</div>

<?= $this->render('/lego/_search', ['model' => $searchModel]) ?>

<?= $this->render('/lego/_list', ['dataProvider' => $dataProvider]) ?>

<?= $this->render('_presentation', ['model' => $subTheme ?? $theme]) ?>
