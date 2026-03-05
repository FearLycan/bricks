<?php

use common\components\Html;
use frontend\components\Helper;
use frontend\components\SeoHelper;
use frontend\components\T;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var string             $number
 * @var string             $name
 * @var string             $image
 */

$displayName = $name !== '' ? $name : $number;
$page = SeoHelper::resolvePageNumber();

$this->title = SeoHelper::buildMinifigTitle($displayName, $page);
$this->params['metaDescription'] = SeoHelper::buildMinifigDescription($displayName, $number, $page);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(
    $page > 1
        ? ['/lego/lego/minifig', 'number' => $number, 'page' => $page]
        : ['/lego/lego/minifig', 'number' => $number]
);
$this->params['robots'] = 'index,follow';
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = SeoHelper::normalizeText($displayName);

if ($image !== '') {
    $this->params['socialImage'] = SeoHelper::buildAbsoluteUrl($image);
}
?>

<div class="col-lg-12 mt-4">
    <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
    <div class="d-inline-flex align-items-center gap-3 p-2 rounded border bg-white mb-3 col-lg-3 col-md-3">
        <?php if ($image !== ''): ?>
            <?= Html::img($image, [
                    'alt'     => Html::encode($displayName),
                    'loading' => 'lazy',
                    'style'   => 'width: 72px; height: 72px; object-fit: contain; background: #f8f9fa;',
            ]) ?>
        <?php endif; ?>
        <div>
            <div class="fw-semibold"><?= Html::encode($displayName) ?></div>
            <small class="text-body-secondary"><?= Html::encode(T::tr('Minifigure number')) ?>: <?= Html::encode($number) ?></small>
        </div>
    </div>
</div>

<?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>

