<?php

use common\components\Html;
use common\models\Set;
use common\widgets\InlineScript;
use frontend\components\Helper;
use frontend\components\T;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this  View
 * @var $model Set
 */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = ['label' => $model->theme->name, 'url' => ["/lego/theme/{$model->theme->slug}"]];

if ($model->subtheme) {
    $this->params['breadcrumbs'][] = ['label' => $model->subtheme->name, 'url' => ["/lego/theme/{$model->theme->slug}/{$model->subtheme->slug}"]];
}

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="lego-product-page">
    <div class="row g-4 product">
        <div class="col-lg-7">
            <div class="lego-gallery-card">
                <div class="lego-gallery-main">
                    <?= Html::img($model->getDisplayMainImageUrl(), ['class' => 'lego-main-image', 'alt' => Html::encode($model->name), 'id' => 'legoMainImage', 'loading' => 'lazy']) ?>
                </div>

                <?php if ($model->images): ?>
                    <div class="lego-gallery-thumbs">
                        <?php foreach ($model->images as $image): ?>
                            <button class="lego-thumb <?= $model->getDisplayMainImage()?->id === $image->id ? 'is-active' : '' ?>" type="button" data-image-src="<?= Html::encode(Url::to($image->url)) ?>" aria-label="<?= Html::encode($model->name) ?>">
                                <?= Html::img(Url::to($image->url), ['alt' => Html::encode($model->name), 'loading' => 'lazy']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="lego-info-card">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?= Html::a(Html::encode($model->theme->name), ["/lego/theme/{$model->theme->slug}"], [
                            'class' => 'badge rounded-pill text-bg-warning text-dark text-decoration-none',
                    ]) ?>
                    <?php if ($model->subtheme): ?>
                        <?= Html::a(Html::encode($model->subtheme->name), ["/lego/theme/{$model->theme->slug}/{$model->subtheme->slug}"], [
                                'class' => 'badge rounded-pill text-bg-light border text-decoration-none text-body',
                        ]) ?>
                    <?php endif; ?>
                    <span class="badge rounded-pill text-bg-primary">
                        <?= Html::encode($model->getThemeGroupNameOrDefault()) ?>
                    </span>
                </div>
                <h1 class="lego-title"><?= $this->title ?></h1>
                <div class="lego-set-number"><?= T::t('Set') ?> #<?= Html::encode($model->getSetNumberText()) ?></div>
                <div class="lego-price"><?= Html::encode($model->getFormattedPriceOrDefault(T::t('Check price in store'))) ?></div>

                <div class="lego-quick-facts">
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-cake me-1"></i><?= T::t('Age') ?></span>
                        <span class="value"><?= Html::encode($model->getAgeText()) ?></span>
                    </div>
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-columns-gap me-1"></i><?= T::t('Pieces') ?></span>
                        <span class="value"><?= Html::encode($model->getPiecesText()) ?></span>
                    </div>
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-people me-1"></i><?= T::t('Minifigures') ?></span>
                        <span class="value"><?= Html::encode($model->getMinifiguresText()) ?></span>
                    </div>
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-calendar-check me-1"></i><?= T::t('Release year') ?></span>
                        <span class="value"><?= Html::encode($model->getYearText()) ?></span>
                    </div>
                </div>

                <div class="lego-cta-group">
                    <?php if ($model->brickset_url): ?>
                        <?= Html::a(T::t('Open on Brickset'), $model->brickset_url, [
                                'class'  => 'btn btn-primary btn-lg',
                                'target' => '_blank',
                                'rel'    => 'noopener noreferrer',
                        ]) ?>
                    <?php endif; ?>
                    <?= Html::a(T::t('Browse all sets'), ['/lego'], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="lego-details-card">
                <ul class="nav nav-tabs lego-tabs" id="legoProductTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                            <?= T::t('Overview') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="false">
                            <?= T::t('Details') ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4" id="legoProductTabsContent">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab" tabindex="0">
                        <p class="mb-0">
                            <?= T::t('Discover') ?> <strong><?= Html::encode($model->name) ?></strong> <?= T::t('from the') ?> <?= Html::encode($model->theme->name) ?> <?= T::t('theme') ?>.
                            <?= T::t('This set contains') ?> <strong><?= Html::encode($model->getPiecesText()) ?></strong> <?= T::t('pieces and is designed for builders aged') ?>
                            <strong><?= Html::encode($model->getAgeText()) ?></strong>.
                        </p>
                    </div>
                    <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab" tabindex="0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lego-meta-item"><span><?= T::t('Theme') ?></span><strong><?= Html::encode($model->theme->name) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::t('Theme group') ?></span><strong><?= Html::encode($model->getThemeGroupNameOrDefault()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::t('Subtheme') ?></span><strong><?= Html::encode($model->getSubthemeNameOrDefault()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::t('Availability') ?></span><strong><?= Html::encode($model->getAvailabilityText(T::t('No data'))) ?></strong></div>
                            </div>
                            <div class="col-md-6">
                                <div class="lego-meta-item"><span><?= T::t('Set number') ?></span><strong><?= Html::encode($model->getSetNumberText()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::t('Pieces') ?></span><strong><?= Html::encode($model->getPiecesText()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::t('Minifigures') ?></span><strong><?= Html::encode($model->getMinifiguresText()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::t('Dimensions (H x W x D)') ?></span><strong><?= nl2br(Html::encode($model->getDimensionsDisplayText(T::t('No dimensions available')))) ?></strong></div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h5 class="mb-3"><?= T::t('Prices by country') ?></h5>
                            <?php if ($model->setPrices): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                        <tr>
                                            <th><?= T::t('Country') ?></th>
                                            <th><?= T::t('Price') ?></th>
                                            <th><?= T::t('Price per piece') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($model->setPrices as $setPrice): ?>
                                            <tr>
                                                <td><?= Html::encode($setPrice->country_code) ?></td>
                                                <td><?= Html::encode(Set::formatAmountFromCents($setPrice->retail_price_cents, $setPrice->country_code)) ?></td>
                                                <td>
                                                    <?php if ($model->pieces && $model->pieces > 0): ?>
                                                        <?= Html::encode(Set::formatAmountFromCents((int) round($setPrice->retail_price_cents / $model->pieces), $setPrice->country_code)) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-body-secondary mb-0"><?= T::t('No prices available') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php InlineScript::begin(); ?>
<script>
    (() => {
        const mainImage = document.getElementById('legoMainImage');
        const thumbnails = document.querySelectorAll('.lego-thumb');

        if (!mainImage || thumbnails.length === 0) {
            return;
        }

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', () => {
                const targetSrc = thumbnail.dataset.imageSrc;

                if (!targetSrc) {
                    return;
                }

                mainImage.setAttribute('src', targetSrc);

                thumbnails.forEach((item) => {
                    item.classList.remove('is-active');
                });

                thumbnail.classList.add('is-active');
            });
        });
    })();
</script>
<?php InlineScript::end(); ?>
