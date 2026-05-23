<?php

use common\models\Set;
use common\models\SetReview;
use frontend\components\LinkPager;
use frontend\components\T;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View                $this
 * @var ActiveDataProvider  $dataProvider
 * @var SetReview[]         $reviews
 * @var int                 $totalCount
 * @var array<string,string> $dimensionShortLabels
 */

$this->title = T::tr('My reviews');
$this->params['metaDescription'] = T::tr('All reviews you have written on BrickAtlas.');
$this->params['robots'] = 'noindex,nofollow';
$this->params['breadcrumbs'][] = [
    'label' => T::tr('Profile'),
    'url'   => ['/user/profile'],
];
$this->params['breadcrumbs'][] = T::tr('My reviews');

?>

<div class="col-12 mt-4">
    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 mb-3">
        <h1 class="page-title mb-0">
            <i class="bi bi-journal-richtext text-primary me-2"></i>
            <?= Html::encode(T::tr('My reviews')) ?>
        </h1>
        <span class="text-body-secondary small">
            <?= T::tr('{n, plural, =1{# review} other{# reviews}}', ['n' => $totalCount]) ?>
        </span>
    </div>

    <?php if ($totalCount === 0): ?>
        <div class="user-page-card text-center py-5">
            <div class="display-5 mb-2">🌟</div>
            <h5 class="mb-2"><?= Html::encode(T::tr('You have not reviewed any sets yet.')) ?></h5>
            <p class="text-body-secondary mb-3">
                <?= Html::encode(T::tr('Find a set you own (or want) and share what you think.')) ?>
            </p>
            <?= Html::a(
                '<i class="bi bi-search me-1"></i>' . Html::encode(T::tr('Browse sets')),
                Url::to(['/lego/index']),
                ['class' => 'btn btn-primary', 'encode' => false]
            ) ?>
        </div>
    <?php else: ?>
        <div class="my-reviews-list">
            <?php foreach ($reviews as $review): ?>
                <?php
                /** @var Set|null $set */
                $set = $review->set;
                if ($set === null) { continue; }
                ?>
                <?= $this->render('_item', [
                    'review'               => $review,
                    'set'                  => $set,
                    'dimensionShortLabels' => $dimensionShortLabels,
                ]) ?>
            <?php endforeach; ?>
        </div>

        <?php if ($dataProvider->pagination): ?>
            <div class="d-flex justify-content-center mt-4">
                <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="mt-4">
        <?= Html::a(
            '<i class="bi bi-arrow-left me-2"></i>' . T::tr('Back to profile'),
            ['/user/profile'],
            ['class' => 'btn btn-outline-secondary', 'encode' => false]
        ) ?>
    </div>
</div>
