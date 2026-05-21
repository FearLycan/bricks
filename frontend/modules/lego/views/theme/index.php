<?php

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
                ? ["/lego/theme/{$theme->slug}/{$subTheme->slug}", 'page' => $page]
                : ["/lego/theme/{$theme->slug}/{$subTheme->slug}"])
        : ($page > 1
                ? ["/lego/theme/{$theme->slug}", 'page' => $page]
                : ["/lego/theme/{$theme->slug}"])
);
$this->params['robots'] = 'index,follow';

$themeBaseUrl = $subTheme
    ? ["/lego/theme/{$theme->slug}/{$subTheme->slug}"]
    : ["/lego/theme/{$theme->slug}"];
SeoHelper::registerPaginationLinks($this, $dataProvider, $page, $themeBaseUrl);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];

if ($subTheme) {
    $this->params['breadcrumbs'][] = ['label' => $theme->name, 'url' => ["/lego/theme/{$theme->slug}"]];
    $this->params['breadcrumbs'][] = SeoHelper::normalizeText($subTheme->name);
    if ($subTheme->image) {
        $this->params['socialImage'] = SeoHelper::buildAbsoluteUrl($subTheme->image);
    }
} else {
    $this->params['breadcrumbs'][] = SeoHelper::normalizeText($theme->name);
    if ($theme->image) {
        $this->params['socialImage'] = SeoHelper::buildAbsoluteUrl($theme->image);
    }
}

// Full-width layout so the hero banner can span edge to edge; the catalog
// below is wrapped in its own .container. See [[feedback-fullwidth-layout]].
$this->params['fullWidth'] = true;
$this->registerCssFile('@web/css/theme.css', ['depends' => [\frontend\assets\AppAsset::class]]);

?>

<?= JsonLdRenderer::render([ItemListSchemaFactory::fromDataProvider($dataProvider)]) ?>

<?= $this->render('_hero-banner', [
        'theme'    => $theme,
        'subTheme' => $subTheme,
        'intro'    => SeoHelper::buildThemeIntro($theme, $subTheme),
]) ?>

<div class="container">
    <div class="mb-3">
        <?= $this->render('/lego/_search', ['model' => $searchModel]) ?>
    </div>

    <?= $this->render('/lego/_list', ['dataProvider' => $dataProvider]) ?>
</div>
