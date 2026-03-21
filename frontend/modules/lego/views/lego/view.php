<?php

use common\components\Html;
use common\models\Set;
use common\models\User;
use common\schema\builder\SetPageSchemaBuilder;
use common\schema\JsonLdRenderer;
use common\widgets\InlineScript;
use frontend\components\Helper;
use frontend\components\SeoHelper;
use frontend\components\T;
use Yii;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this  View
 * @var $model Set
 * @var $user  User|null
 */

$this->title = SeoHelper::buildSetTitle($model);
$this->params['metaDescription'] = SeoHelper::buildSetDescription($model);
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/lego/lego/view', 'slug' => $model->slug]);
$this->params['robots'] = 'index,follow';
$this->params['ogType'] = 'product';
$this->params['socialImage'] = SeoHelper::buildAbsoluteUrl($model->getDisplayMainImageUrl());
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = ['label' => $model->theme->name, 'url' => ["/lego/theme/{$model->theme->slug}"]];

if ($model->subtheme) {
    $this->params['breadcrumbs'][] = ['label' => $model->subtheme->name, 'url' => ["/lego/theme/{$model->theme->slug}/{$model->subtheme->slug}"]];
}

$this->params['breadcrumbs'][] = SeoHelper::normalizeText($model->name);
$bestOffer = $model->getBestAlternativeOffer('USD');
$promoPriceUsd = $model->getFormattedPromotionalPrice('USD');
$basePriceUsd = $model->getFormattedPrice('USD');
$savingsPercent = $model->getPromotionalSavingsPercent('USD');
$bestOfferId = $bestOffer?->id;

?>

<?= JsonLdRenderer::render(SetPageSchemaBuilder::build($model, Url::current([], true))) ?>

<div class="lego-product-page">
    <div class="row g-4 product">
        <div class="col-lg-7 lego-set">
            <div class="lego-gallery-card">
                <div class="lego-gallery-main">
                    <?php if ($model->images && count($model->images) > 1): ?>
                        <button type="button" class="lego-gallery-nav lego-gallery-nav-prev"
                                id="legoGalleryPrev"
                                aria-label="<?= Html::encode(T::tr('Previous image')) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                    <?php endif; ?>

                    <?= Html::img($model->getDisplayMainImageUrl(), ['class' => 'lego-main-image', 'alt' => Html::encode($model->name), 'id' => 'legoMainImage', 'loading' => 'lazy']) ?>

                    <?php if ($model->images && count($model->images) > 1): ?>
                        <button type="button" class="lego-gallery-nav lego-gallery-nav-next"
                                id="legoGalleryNext"
                                aria-label="<?= Html::encode(T::tr('Next image')) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($model->images): ?>
                    <div class="lego-gallery-thumbs is-collapsed" id="legoGalleryThumbs">
                        <?php foreach ($model->images as $image): ?>
                            <button class="lego-thumb <?= $model->getDisplayMainImage()?->id === $image->id ? 'is-active' : '' ?>" type="button" data-image-src="<?= Html::encode(Url::to($image->url)) ?>" aria-label="<?= Html::encode($model->name) ?>">
                                <?= Html::img(Url::to($image->url), ['alt' => Html::encode($model->name), 'loading' => 'lazy']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <button
                            type="button"
                            class="btn btn-link btn-sm px-0 mt-2 text-decoration-none lego-gallery-thumbs-toggle d-none"
                            id="legoGalleryThumbsToggle"
                            aria-expanded="false">
                        <span class="label-more"><?= T::tr('Show more') ?></span>
                        <span class="label-less"><?= T::tr('Show less') ?></span>
                    </button>
                <?php endif; ?>

            </div>
        </div>

        <div class="col-lg-5 lego-set-information">
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
                <h1 class="lego-title"><?= Html::encode($model->name) ?></h1>
                <div class="lego-set-number"><?= T::tr('Set') ?> #<?= Html::encode($model->getSetNumberText()) ?></div>
                <div class="lego-price">
                    <?php if ($basePriceUsd !== null && $promoPriceUsd !== null): ?>
                        <span class="text-body-secondary text-decoration-line-through me-2"><?= Html::encode($basePriceUsd) ?></span>
                        <span class="text-success fw-bold"><?= Html::encode($promoPriceUsd) ?></span>
                        <?php if ($savingsPercent !== null && $savingsPercent > 0): ?>
                            <span class="badge text-bg-danger ms-2">-<?= Html::encode((string)$savingsPercent) ?>%</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= Html::encode($model->getFormattedPriceOrDefault(T::tr('Check price in store'), 'USD')) ?>
                    <?php endif; ?>
                </div>
                <?php if ($basePriceUsd !== null): ?>
                    <div class="small text-body-secondary mb-2">
                        <?= T::tr('Compared with the official LEGO retail price.') ?>
                    </div>
                <?php endif; ?>

                <?php if ($bestOffer): ?>
                    <div class="small text-success mb-3">
                        <i class="bi bi-tag-fill me-1"></i>
                        <?= T::tr('Best offer') ?>:
                        <strong><?= Html::encode($bestOffer->store->name ?? T::tr('Unknown store')) ?></strong>
                        (<?= Html::encode($bestOffer->getFormattedPriceOrDefault(T::tr('No price'))) ?>)
                    </div>
                <?php endif; ?>

                <div class="lego-quick-facts">
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-cake me-1"></i><?= T::tr('Age') ?></span>
                        <span class="value"><?= Html::encode($model->getAgeText()) ?></span>
                    </div>
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-columns-gap me-1"></i><?= T::tr('Pieces') ?></span>
                        <span class="value"><?= Html::encode($model->getPiecesText()) ?></span>
                    </div>
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-people me-1"></i><?= T::tr('Minifigures') ?></span>
                        <span class="value"><?= Html::encode($model->getMinifiguresText()) ?></span>
                    </div>
                    <div class="lego-quick-fact">
                        <span class="label"><i class="bi bi-calendar-check me-1"></i><?= T::tr('Release year') ?></span>
                        <span class="value"><?= Html::encode($model->getYearText()) ?></span>
                    </div>
                </div>

                <?php if ($model->tagModels): ?>
                    <?php $tagToggleId = 'tags-toggle-' . $model->id; ?>
                    <div class="mb-3">
                        <h6 class="mb-2"><?= T::tr('Tags') ?></h6>
                        <input type="checkbox" class="lego-tags-toggle-input d-none" id="<?= Html::encode($tagToggleId) ?>">
                        <div class="d-flex flex-wrap gap-2 lego-tags-list is-collapsed">
                            <?php foreach ($model->tagModels as $tagModel): ?>
                                <span class="badge rounded-pill text-bg-secondary border"><?= Html::encode($tagModel->name) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($model->tagModels) > 16): ?>
                            <label for="<?= Html::encode($tagToggleId) ?>" class="btn btn-link btn-sm p-0 mt-2 text-decoration-none lego-tags-toggle-label">
                                <span class="label-more"><?= T::tr('Show more') ?></span>
                                <span class="label-less"><?= T::tr('Show less') ?></span>
                            </label>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="lego-side-actions">
                    <div class="lego-cta-group">
                        <?= Html::a(T::tr('Browse all sets'), ['/lego'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= Html::a(T::tr('Check official LEGO price'), "https://www.lego.com/search?q={$model->number}", [
                                'class'  => 'btn btn-lego',
                                'target' => '_blank',
                                'rel'    => 'noopener noreferrer',
                        ]) ?>
                        <?= Html::a(T::tr('Compare all offers'), '#alternative-offers', ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 alternative-offers" id="alternative-offers">
            <div class="lego-details-card">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="mb-0"><?= T::tr('Alternative offers by store') ?></h5>
                    <?php if ($user?->isAdmin()): ?>
                        <?= Html::a(T::tr('Add offer'), Yii::$app->backendUrlManager->createAbsoluteUrl([
                                '/admin/set-offer/create',
                                'setId' => (int)$model->id,]), [
                                'class'  => 'btn btn-sm btn-outline-primary',
                                'target' => '_blank',
                                'rel'    => 'noopener noreferrer',
                        ]) ?>
                    <?php endif; ?>
                </div>
                <p class="small text-body-secondary mb-3">
                    <?= T::tr('These offers can be lower than the official LEGO price. Marketplace listings may vary by seller, shipping cost, taxes, and stock availability.') ?>
                </p>
                <?php if ($model->setOffers): ?>
                    <div class="row row-cols-1 row-cols-lg-2 g-3">
                        <?php foreach ($model->setOffers as $offer): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm border-0 lego-offer-card<?= $bestOfferId !== null && $offer->id === $bestOfferId ? ' is-best-offer' : '' ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3">
                                            <?php if ($offer->image): ?>
                                                <div class="flex-shrink-0">
                                                    <?= Html::img($offer->image, [
                                                            'class'         => 'rounded border js-zoomable-image',
                                                            'alt'           => Html::encode($offer->getDisplayNameOrDefault($model->name)),
                                                            'loading'       => 'lazy',
                                                            'data-zoom-src' => $offer->image,
                                                            'style'         => 'width: 84px; height: 84px; object-fit: contain; background-color: #f8f9fa;',
                                                    ]) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between gap-3">
                                                    <div>
                                                        <div class="fw-semibold d-flex align-items-center gap-2">
                                                            <?php if ($offer->store && $offer->store->logo): ?>
                                                                <?= Html::img($offer->store->logo, [
                                                                        'alt'     => Html::encode($offer->store->name ?? T::tr('Store logo')),
                                                                        'loading' => 'lazy',
                                                                        'style'   => 'width: 22px; height: 22px; object-fit: contain;',
                                                                ]) ?>
                                                            <?php endif; ?>
                                                            <?= Html::encode($offer->store->name ?? T::tr('Unknown store')) ?>
                                                        </div>
                                                        <div class="small text-body-secondary">
                                                            <?= Html::encode($offer->getDisplayNameOrDefault($model->name)) ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                                            <div class="fw-bold"><?= Html::encode($offer->getFormattedPriceOrDefault(T::tr('No price'))) ?></div>
                                                            <?php if ($model->price !== null && $model->price > 0 && $offer->price !== null && $offer->price > 0 && strtoupper((string)$offer->currency_code) === 'USD' && $offer->price < $model->price): ?>
                                                                <?php $offerSavingsPercent = (int)round((($model->price - $offer->price) / $model->price) * 100); ?>
                                                                <span class="badge text-bg-dark">-<?= Html::encode((string)$offerSavingsPercent) ?>%</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="small text-body-secondary">
                                                            <?= T::tr('In stock') ?>
                                                        </div>
                                                        <div class="small text-body-secondary d-none">
                                                            <?= T::tr('Price per piece') ?>:
                                                            <?php if ($model->pieces && $model->pieces > 0 && $offer->price !== null): ?>
                                                                <?= Html::encode(Set::formatAmountFromCents((int)round($offer->price / $model->pieces), $offer->currency_code ?: 'USD')) ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-3 d-flex gap-3 align-items-center flex-wrap">
                                                            <span class="badge text-bg-warning text-dark">
                                                                <i class="bi bi-star-fill me-1"></i>
                                                                <?= Html::encode($offer->rating_value !== null ? number_format((float)$offer->rating_value, 1, '.', '') : '0.0') ?>
                                                            </span>
                                                    <span class="small text-body-secondary">
                                                                <?= T::tr('Reviews') ?>: <?= Html::encode((string)$offer->review_count) ?>
                                                            </span>
                                                    <?php if ($offer->url): ?>
                                                        <?= Html::a('<i class="bi bi-bag-check me-1"></i>' . T::tr('View offer'), $offer->url, ['class' => 'btn btn-sm btn-success ms-auto w-50', 'target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($offer->setOfferReviews): ?>
                                            <div class="mt-3 pt-3 border-top">
                                                <?php foreach (array_slice($offer->setOfferReviews, 0, 3) as $review): ?>
                                                    <div class="mb-2">
                                                        <div class="small fw-semibold">
                                                            <?= Html::encode($review->author_name ?: T::tr('Anonymous')) ?>
                                                            <?php if ($review->rating_value !== null): ?>
                                                                <span class="text-body-secondary">· <?= Html::encode(number_format((float)$review->rating_value, 1, '.', '')) ?>/<?= Html::encode((string)($review->rating_scale_max ?? 5)) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($review->title): ?>
                                                            <div class="small"><?= Html::encode($review->title) ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($review->content): ?>
                                                            <div class="small text-body-secondary"><?= Html::encode(mb_substr(trim($review->content), 0, 180)) ?><?= mb_strlen(trim($review->content)) > 180 ? '...' : '' ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-body-secondary mb-0"><?= T::tr('No alternative offers available') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 lego-set-details">
            <div class="lego-details-card">
                <ul class="nav nav-tabs lego-tabs" id="legoProductTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                            <?= T::tr('Overview') ?>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="details-tab" data-bs-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="false">
                            <?= T::tr('Details') ?>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="minifigures-tab" data-bs-toggle="tab" href="#minifigures" role="tab" aria-controls="minifigures" aria-selected="false">
                            <?= T::tr('Minifigures <small>({n})</small>', ['n' => $model->minifigures]) ?>
                        </a>
                    </li>
                </ul>

                <div class="tab-content pt-4" id="legoProductTabsContent">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab" tabindex="0">
                        <p class="mb-0">
                            <?= T::tr('Discover') ?> <strong><?= Html::encode($model->name) ?></strong> <?= T::tr('from the') ?> <?= Html::encode($model->theme->name) ?> <?= T::tr('theme') ?>.
                            <?= T::tr('This set contains') ?> <strong><?= Html::encode($model->getPiecesText()) ?></strong> <?= T::tr('pieces and is designed for builders aged') ?>
                            <strong><?= Html::encode($model->getAgeText()) ?></strong>.
                        </p>
                        <?php if ($model->description): ?>
                            <div class="mt-3">
                                <?= HtmlPurifier::process($model->description) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab" tabindex="0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lego-meta-item"><span><?= T::tr('Theme') ?></span><strong><?= Html::encode($model->theme->name) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::tr('Theme group') ?></span><strong><?= Html::encode($model->getThemeGroupNameOrDefault()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::tr('Subtheme') ?></span><strong><?= Html::encode($model->getSubthemeNameOrDefault()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::tr('Availability') ?></span><strong><?= Html::encode($model->getAvailabilityText(T::tr('No data'))) ?></strong></div>
                            </div>
                            <div class="col-md-6">
                                <div class="lego-meta-item"><span><?= T::tr('Set number') ?></span><strong><?= Html::encode($model->getSetNumberText()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::tr('Pieces') ?></span><strong><?= Html::encode($model->getPiecesText()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::tr('Minifigures') ?></span><strong><?= Html::encode($model->getMinifiguresText()) ?></strong></div>
                                <div class="lego-meta-item"><span><?= T::tr('Dimensions (H x W x D)') ?></span><strong><?= nl2br(Html::encode($model->getDimensionsDisplayText(T::tr('No dimensions available')))) ?></strong></div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h5 class="mb-3"><?= T::tr('Prices by country') ?></h5>
                            <?php if ($model->setPrices): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                        <tr>
                                            <th><?= T::tr('Country') ?></th>
                                            <th><?= T::tr('Price') ?></th>
                                            <th><?= T::tr('Price per piece') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($model->setPrices as $setPrice): ?>
                                            <tr>
                                                <td><?= Html::encode($setPrice->country_code) ?></td>
                                                <td><?= Html::encode(Set::formatAmountFromCents($setPrice->retail_price_cents, $setPrice->country_code)) ?></td>
                                                <td>
                                                    <?php if ($model->pieces && $model->pieces > 0): ?>
                                                        <?= Html::encode(Set::formatAmountFromCents((int)round($setPrice->retail_price_cents / $model->pieces), $setPrice->country_code)) ?>
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
                                <p class="text-body-secondary mb-0"><?= T::tr('No prices available') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="minifigures" role="tabpanel" aria-labelledby="minifigures-tab" tabindex="0">
                        <div class="mt-1">
                            <h5 class="mb-3"><?= T::tr('Minifigures in this set') ?></h5>
                            <?php if ($model->setMinifigs): ?>
                                <div class="row row-cols-2 row-cols-lg-4 g-3">
                                    <?php foreach ($model->setMinifigs as $minifig): ?>
                                        <div class="col">
                                            <div class="card h-100 shadow-sm border-0">
                                                <?php if ($minifig->image): ?>
                                                    <?= Html::img($minifig->image, [
                                                            'class'         => 'card-img-top js-zoomable-image',
                                                            'alt'           => Html::encode($minifig->name),
                                                            'loading'       => 'lazy',
                                                            'data-zoom-src' => $minifig->image,
                                                            'style'         => 'height: 190px; object-fit: contain; background-color: #f8f9fa;',
                                                    ]) ?>
                                                <?php endif; ?>
                                                <div class="card-body d-flex flex-column">
                                                    <div class="fw-semibold mb-3">
                                                        <?= Html::a(Html::encode($minifig->name), ["/lego/minifig/{$minifig->number}"], ['class' => 'text-decoration-none']) ?>
                                                    </div>
                                                    <div class="mt-auto">
                                                        <span class="badge text-bg-primary"><?= T::tr('Qty') ?>: <?= Html::encode((string)$minifig->quantity) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-body-secondary mb-0"><?= T::tr('No minifigures available') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center position-relative">
                <img id="imageZoomModalImage" src="" alt="" class="img-fluid rounded">
                <button id="imageZoomPrev" type="button" class="btn btn-light position-absolute top-50 start-0 translate-middle-y ms-2" aria-label="Previous image">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button id="imageZoomNext" type="button" class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-2" aria-label="Next image">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php InlineScript::begin(); ?>
<script>
    (() => {
        const mainImage = document.getElementById('legoMainImage');
        const thumbnails = document.querySelectorAll('.lego-thumb');
        const zoomableImages = document.querySelectorAll('.js-zoomable-image');
        const tabTriggers = document.querySelectorAll('#legoProductTabs [data-bs-toggle="tab"]');
        const zoomModalElement = document.getElementById('imageZoomModal');
        const zoomModalImage = document.getElementById('imageZoomModalImage');
        const zoomPrev = document.getElementById('imageZoomPrev');
        const zoomNext = document.getElementById('imageZoomNext');
        const galleryThumbs = document.getElementById('legoGalleryThumbs');
        const galleryThumbsToggle = document.getElementById('legoGalleryThumbsToggle');
        const galleryPrev = document.getElementById('legoGalleryPrev');
        const galleryNext = document.getElementById('legoGalleryNext');
        const galleryMain = document.querySelector('.lego-gallery-main');

        if (typeof bootstrap !== 'undefined' && tabTriggers.length > 0) {
            const activateTabFromHash = () => {
                const {hash} = window.location;
                if (!hash) {
                    return;
                }

                const trigger = document.querySelector(`#legoProductTabs [href="${hash}"]`);
                if (trigger) {
                    bootstrap.Tab.getOrCreateInstance(trigger).show();
                }
            };

            tabTriggers.forEach((trigger) => {
                trigger.addEventListener('shown.bs.tab', () => {
                    const targetHash = trigger.getAttribute('href');
                    if (targetHash) {
                        history.replaceState(null, '', targetHash);
                    }
                });
            });

            activateTabFromHash();
        }

        if (zoomModalElement && zoomModalImage && zoomPrev && zoomNext && typeof bootstrap !== 'undefined') {
            const zoomModal = new bootstrap.Modal(zoomModalElement);
            let currentIndex = 0;
            let currentSources = [];

            const getImageSources = () => {
                const sources = [];
                zoomableImages.forEach((image) => {
                    const source = image.dataset.zoomSrc || image.getAttribute('src');
                    if (source && !sources.includes(source)) {
                        sources.push(source);
                    }
                });

                return sources;
            };

            const renderCurrentImage = () => {
                if (!currentSources[currentIndex]) {
                    return;
                }

                zoomModalImage.setAttribute('src', currentSources[currentIndex]);
                zoomPrev.disabled = currentSources.length <= 1;
                zoomNext.disabled = currentSources.length <= 1;
            };

            zoomableImages.forEach((image) => {
                image.addEventListener('click', () => {
                    const zoomSrc = image.dataset.zoomSrc || image.getAttribute('src');
                    if (!zoomSrc) {
                        return;
                    }

                    currentSources = getImageSources();
                    const clickedIndex = currentSources.indexOf(zoomSrc);
                    currentIndex = clickedIndex >= 0 ? clickedIndex : 0;
                    renderCurrentImage();
                    zoomModalImage.setAttribute('alt', image.getAttribute('alt') || '');
                    zoomModal.show();
                });
            });

            zoomPrev.addEventListener('click', () => {
                if (currentSources.length <= 1) {
                    return;
                }

                currentIndex = (currentIndex - 1 + currentSources.length) % currentSources.length;
                renderCurrentImage();
            });

            zoomNext.addEventListener('click', () => {
                if (currentSources.length <= 1) {
                    return;
                }

                currentIndex = (currentIndex + 1) % currentSources.length;
                renderCurrentImage();
            });

            zoomModalElement.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowLeft') {
                    zoomPrev.click();
                }

                if (event.key === 'ArrowRight') {
                    zoomNext.click();
                }
            });
        }

        if (mainImage && thumbnails.length > 0) {
            const normalizeUrl = (value) => {
                if (!value) {
                    return '';
                }

                try {
                    return new URL(value, window.location.origin).href;
                } catch (error) {
                    return String(value);
                }
            };

            const getThumbnailSources = () => {
                return Array.from(thumbnails)
                    .map((thumbnail) => thumbnail.dataset.imageSrc || '')
                    .filter((source) => source !== '');
            };

            const setMainGalleryImage = (targetSrc) => {
                if (!targetSrc) {
                    return;
                }

                mainImage.setAttribute('src', targetSrc);
                mainImage.dataset.zoomSrc = targetSrc;

                const normalizedTarget = normalizeUrl(targetSrc);
                thumbnails.forEach((item) => {
                    item.classList.toggle('is-active', normalizeUrl(item.dataset.imageSrc || '') === normalizedTarget);
                });
            };

            thumbnails.forEach((thumbnail) => {
                thumbnail.addEventListener('click', () => {
                    const targetSrc = thumbnail.dataset.imageSrc;

                    setMainGalleryImage(targetSrc);
                });
            });

            const moveGalleryBy = (step) => {
                const sources = getThumbnailSources();
                if (sources.length < 2) {
                    return;
                }

                const currentSrc = normalizeUrl(mainImage.getAttribute('src') || '');
                const currentIndex = sources.findIndex((source) => normalizeUrl(source) === currentSrc);
                const safeIndex = currentIndex >= 0 ? currentIndex : 0;
                const nextIndex = (safeIndex + step + sources.length) % sources.length;
                setMainGalleryImage(sources[nextIndex]);
            };

            if (galleryPrev) {
                galleryPrev.addEventListener('click', () => {
                    moveGalleryBy(-1);
                });
            }

            if (galleryNext) {
                galleryNext.addEventListener('click', () => {
                    moveGalleryBy(1);
                });
            }

            if (galleryMain) {
                let touchStartX = 0;
                let touchStartY = 0;

                galleryMain.addEventListener('touchstart', (event) => {
                    const touch = event.changedTouches[0];
                    if (!touch) {
                        return;
                    }

                    touchStartX = touch.clientX;
                    touchStartY = touch.clientY;
                }, {passive: true});

                galleryMain.addEventListener('touchend', (event) => {
                    const touch = event.changedTouches[0];
                    if (!touch) {
                        return;
                    }

                    const deltaX = touch.clientX - touchStartX;
                    const deltaY = touch.clientY - touchStartY;
                    const absX = Math.abs(deltaX);
                    const absY = Math.abs(deltaY);
                    const swipeThreshold = 40;

                    if (absX < swipeThreshold || absX <= absY) {
                        return;
                    }

                    moveGalleryBy(deltaX < 0 ? 1 : -1);
                }, {passive: true});
            }
        }

        if (galleryThumbs && galleryThumbsToggle) {
            const refreshGalleryToggle = () => {
                const hasOverflow = galleryThumbs.scrollHeight > galleryThumbs.clientHeight + 1;
                galleryThumbsToggle.classList.toggle('d-none', !hasOverflow);
            };

            galleryThumbsToggle.addEventListener('click', () => {
                const isExpanded = galleryThumbs.classList.toggle('is-expanded');
                galleryThumbs.classList.toggle('is-collapsed', !isExpanded);
                galleryThumbsToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            });

            window.addEventListener('resize', refreshGalleryToggle);
            refreshGalleryToggle();
        }
    })();
</script>
<?php InlineScript::end(); ?>
