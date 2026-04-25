<?php

use common\components\Html;
use common\models\SetOffer;
use common\widgets\InlineScript;
use frontend\components\T;

/**
 * @var SetOffer                                   $offer
 * @var float|null                                 $averageRating
 * @var int                                        $reviewsTotal
 * @var string[]                                   $ratingStarClasses
 * @var array<int, array{label: string, num: int}> $reviewImpressions
 */
?>
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <?= T::tr('Offer reviews') ?>
                <?php if ($offer->store): ?>
                    - <?= Html::encode($offer->store->name) ?>
                <?php endif; ?>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Html::encode(T::tr('Close')) ?>"></button>
        </div>
        <div class="modal-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-2 border-bottom">
                <span class="fw-semibold"><?= T::tr('Rating') ?></span>
                <span class="h5 mb-0"><?= Html::encode($averageRating !== null ? number_format((float)$averageRating, 1, '.', '') : '0.0') ?></span>
                <span class="text-warning">
                    <?php foreach ($ratingStarClasses as $iconClass): ?>
                        <i class="bi <?= Html::encode($iconClass) ?>"></i>
                    <?php endforeach; ?>
                </span>
                <span class="text-body-secondary"><?= Html::encode((string)$reviewsTotal) ?> <?= T::tr('reviews') ?></span>
            </div>
            <?php if ($reviewImpressions !== []): ?>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($reviewImpressions as $impression): ?>
                        <span class="badge rounded-pill text-bg-light border px-3 py-2">
                            <?= Html::encode($impression['label']) ?> (<?= Html::encode((string)$impression['num']) ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!$offer->setOfferReviews): ?>
                <p class="text-body-secondary mb-0"><?= T::tr('No reviews available') ?></p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($offer->setOfferReviews as $review): ?>
                        <div class="border rounded p-3" data-key="<?= Html::encode($review->id) ?>">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <span class="fw-semibold">
                                    <?= Html::encode($review->author_name ?: T::tr('Anonymous')) ?>
                                </span>
                                <?php if ($review->reviewer_country): ?>
                                    <span class="badge text-bg-light border"><?= Html::encode($review->reviewer_country) ?></span>
                                <?php endif; ?>
                                <?php if ($review->rating_value !== null): ?>
                                    <span class="small text-body-secondary">
                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                        <?= Html::encode(number_format((float)$review->rating_value, 1, '.', '')) ?>/<?= Html::encode((string)($review->rating_scale_max ?? 5)) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($review->reviewed_at): ?>
                                    <span class="small text-body-secondary ms-auto">
                                        <i class="bi bi-calendar3 me-1"></i><?= Html::encode(date('Y-m-d', strtotime((string)$review->reviewed_at))) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if ($review->title): ?>
                                <div class="fw-semibold mb-1"><?= Html::encode($review->title) ?></div>
                            <?php endif; ?>

                            <?php if ($review->content): ?>
                                <div class="small text-body-secondary mb-2"><?= nl2br(Html::encode((string)$review->content)) ?></div>
                            <?php endif; ?>

                            <?php if ($review->setOfferReviewImages): ?>
                                <div class="d-flex flex-wrap gap-2 offer-review-images">
                                    <?php foreach ($review->setOfferReviewImages as $reviewImage): ?>
                                        <?= Html::a(
                                                Html::img($reviewImage->url, [
                                                        'alt'     => Html::encode(T::tr('Review image')),
                                                        'loading' => 'lazy',
                                                        'style'   => 'width: 64px; height: 64px; object-fit: cover;',
                                                        'class'   => 'rounded border',
                                                ]),
                                                '#',
                                                [
                                                        "data-href"  => $reviewImage->url,
                                                        "data-gall"  => "review{$review->id}",
                                                        "data-title" => $review->content ?: '',
                                                        'rel'        => 'noopener noreferrer',
                                                        'class'      => 'venobox',
                                                ]
                                        ) ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php InlineScript::begin(); ?>
<script>
    initVenoBox();
</script>
<?php InlineScript::end(); ?>
