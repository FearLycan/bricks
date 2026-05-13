<?php

use common\models\Set;
use common\models\Wishlist;
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

$this->title = T::tr('My wishlist');
$this->params['metaDescription'] = T::tr('LEGO sets saved to your personal wishlist.');
$this->params['robots'] = 'noindex,nofollow';
$this->params['breadcrumbs'][] = T::tr('Wishlist');

$removeUrl = Url::to(['/wishlist/remove']);

/** @var Wishlist[] $items */
$items = $dataProvider->getModels();
$hasItems = !empty($items);

?>

<div class="col-12 mt-4">
    <h1 class="page-title mb-3">
        <i class="bi bi-heart-fill text-danger me-2"></i>
        <?= Html::encode($this->title) ?>
    </h1>

    <div class="wishlist-empty js-wishlist-empty-template <?= $hasItems ? 'd-none' : '' ?>">
        <div class="wishlist-empty-icon"><i class="bi bi-heart"></i></div>
        <p class="mb-2 fw-semibold"><?= Html::encode(T::tr('Your wishlist is empty')) ?></p>
        <p class="mb-3"><?= Html::encode(T::tr('Browse the catalog and tap the heart icon on any set to save it here.')) ?></p>
        <?= Html::a(T::tr('Browse LEGO sets'), ['/lego'], ['class' => 'btn btn-primary btn-sm']) ?>
    </div>

    <div class="js-wishlist-list <?= !$hasItems ? 'd-none' : '' ?>">
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
            <div class="wishlist-item js-wishlist-item" data-set-id="<?= (int)$set->id ?>">
                <a class="wishlist-item-thumb" href="<?= Html::encode($setUrl) ?>">
                    <img src="<?= Html::encode($imageUrl) ?>" alt="<?= Html::encode((string)$set->name) ?>" loading="lazy">
                </a>
                <div class="wishlist-item-body">
                    <p class="wishlist-item-number"><?= Html::encode($set->getSetNumberText()) ?></p>
                    <h2 class="wishlist-item-title">
                        <a href="<?= Html::encode($setUrl) ?>"><?= Html::encode((string)$set->name) ?></a>
                    </h2>
                </div>
                <button type="button"
                        class="js-wishlist-remove wishlist-item-remove"
                        data-set-id="<?= (int)$set->id ?>"
                        data-remove-url="<?= Html::encode($removeUrl) ?>"
                        aria-label="<?= Html::encode(T::tr('Remove from wishlist')) ?>">
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
