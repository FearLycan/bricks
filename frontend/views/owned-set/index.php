<?php

use common\models\OwnedSet;
use common\models\Set;
use frontend\components\LinkPager;
use frontend\components\T;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 */

$this->title = T::tr('My owned sets');
$this->params['metaDescription'] = T::tr('LEGO sets you already own.');
$this->params['robots'] = 'noindex,nofollow';
$this->params['breadcrumbs'][] = T::tr('Owned sets');

$removeUrl = Url::to(['/owned-set/remove']);

/** @var OwnedSet[] $items */
$items = $dataProvider->getModels();
$hasItems = !empty($items);

?>

<div class="col-12 mt-4">
    <h1 class="page-title mb-3">
        <i class="bi bi-box-seam-fill text-primary me-2"></i>
        <?= Html::encode($this->title) ?>
    </h1>

    <div class="owned-set-empty js-owned-set-empty-template <?= $hasItems ? 'd-none' : '' ?>">
        <div class="owned-set-empty-icon"><i class="bi bi-box-seam"></i></div>
        <p class="mb-2 fw-semibold"><?= Html::encode(T::tr('You have no owned sets yet')) ?></p>
        <p class="mb-3"><?= Html::encode(T::tr('Browse the catalog and tap the box icon on any set you already own.')) ?></p>
        <?= Html::a(T::tr('Browse LEGO sets'), ['/lego'], ['class' => 'btn btn-primary btn-sm']) ?>
    </div>

    <div class="js-owned-set-list <?= !$hasItems ? 'd-none' : '' ?>">
        <?php foreach ($items as $item): ?>
            <?php
            /** @var Set|null $set */
            $set = $item->set;
            if (!$set) {
                continue;
            }
            $imageUrl = $set->getDisplayMainImageUrl();
            $setUrl = '/lego/' . $set->slug;
            ?>
            <div class="owned-set-item js-owned-set-item" data-set-id="<?= (int)$set->id ?>">
                <a class="owned-set-item-thumb" href="<?= Html::encode($setUrl) ?>">
                    <img src="<?= Html::encode($imageUrl) ?>" alt="<?= Html::encode((string)$set->name) ?>" loading="lazy">
                </a>
                <div class="owned-set-item-body">
                    <p class="owned-set-item-number"><?= Html::encode($set->getSetNumberText()) ?></p>
                    <h2 class="owned-set-item-title">
                        <a href="<?= Html::encode($setUrl) ?>"><?= Html::encode((string)$set->name) ?></a>
                    </h2>
                </div>
                <button type="button"
                        class="js-owned-set-remove owned-set-item-remove"
                        data-set-id="<?= (int)$set->id ?>"
                        data-remove-url="<?= Html::encode($removeUrl) ?>"
                        aria-label="<?= Html::encode(T::tr('Remove from owned sets')) ?>">
                    <i class="bi bi-trash"></i>
                    <span><?= Html::encode(T::tr('Remove')) ?></span>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($dataProvider->getPagination() !== false && $dataProvider->getPagination()->pageCount > 1): ?>
        <div class="mt-3">
            <?= LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
            ]) ?>
        </div>
    <?php endif; ?>
</div>
