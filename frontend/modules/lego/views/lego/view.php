<?php

use common\components\Html;
use common\models\Set;
use common\widgets\InlineScript;
use frontend\components\Helper;
use frontend\components\T;
use yii\helpers\HtmlPurifier;
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
                <div class="lego-set-number"><?= T::tr('Set') ?> #<?= Html::encode($model->getSetNumberText()) ?></div>
                <div class="lego-price"><?= Html::encode($model->getFormattedPriceOrDefault(T::tr('Check price in store'), 'USD')) ?></div>

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

                <?php if ($model->description): ?>
                    <div class="mb-3">
                        <h6 class="mb-2"><?= T::tr('Description') ?></h6>
                        <div class="small text-body-secondary">
                            <?= Html::encode(mb_substr(trim(strip_tags($model->description)), 0, 240)) ?>...
                        </div>
                    </div>
                <?php endif; ?>

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


                <div class="lego-cta-group">
                    <?= Html::a(T::tr('Browse all sets'), ['/lego'], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                    <?= Html::a(T::tr('Got Lego Store'), " https://www.lego.com/search?q={$model->number}", [
                            'class'  => 'btn btn-lego btn-lg',
                            'target' => '_blank',
                            'rel'    => 'noopener noreferrer',
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="lego-details-card">
                <ul class="nav nav-tabs lego-tabs" id="legoProductTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                            <?= T::tr('Overview') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="false">
                            <?= T::tr('Details') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="minifigures-tab" data-bs-toggle="tab" data-bs-target="#minifigures" type="button" role="tab" aria-controls="minifigures" aria-selected="false">
                            <?= T::tr('Minifigures <small>({n})</small>', ['n' => $model->minifigures]) ?>
                        </button>
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
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
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
                                                        <?= Html::a(Html::encode($minifig->name), ['/lego/lego/minifig', 'number' => $minifig->number], ['class' => 'text-decoration-none']) ?>
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
        const zoomModalElement = document.getElementById('imageZoomModal');
        const zoomModalImage = document.getElementById('imageZoomModalImage');
        const zoomPrev = document.getElementById('imageZoomPrev');
        const zoomNext = document.getElementById('imageZoomNext');

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
            thumbnails.forEach((thumbnail) => {
                thumbnail.addEventListener('click', () => {
                    const targetSrc = thumbnail.dataset.imageSrc;

                    if (!targetSrc) {
                        return;
                    }

                    mainImage.setAttribute('src', targetSrc);
                    mainImage.dataset.zoomSrc = targetSrc;

                    thumbnails.forEach((item) => {
                        item.classList.remove('is-active');
                    });

                    thumbnail.classList.add('is-active');
                });
            });
        }
    })();
</script>
<?php InlineScript::end(); ?>
