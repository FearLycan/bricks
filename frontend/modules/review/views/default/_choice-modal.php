<?php

use common\components\Html;
use common\models\Set;
use common\models\SetReview;
use frontend\components\T;
use yii\helpers\Url;

/**
 * @var Set            $set
 * @var SetReview|null $existing
 */

$simpleUrl   = Url::to(['/review/default/simple-modal', 'setId' => (int)$set->id]);
$detailedUrl = Url::to(['/review/default/detailed-modal', 'setId' => (int)$set->id]);
?>
<div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content review-modal">
        <div class="modal-header border-0 pb-0">
            <div class="w-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="modal-title d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-stars text-warning"></i>
                            <?= $existing ? T::tr('Update your review') : T::tr('Rate this set') ?>
                        </h5>
                        <div class="small text-body-secondary">
                            <?= Html::encode($set->name) ?>
                            <?php if ($set->getSetNumberText('') !== ''): ?>
                                · #<?= Html::encode($set->getSetNumberText()) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Html::encode(T::tr('Close')) ?>"></button>
                </div>
            </div>
        </div>

        <div class="modal-body">
            <p class="text-body-secondary small mb-3 text-center">
                <?= T::tr('Pick how you want to rate. You can change it any time.') ?>
            </p>
            <div class="row g-3 review-choice-grid">
                <div class="col-md-6">
                    <a href="<?= Html::encode($simpleUrl) ?>" class="review-choice-card js-load-modal" data-target="#mainModal">
                        <div class="review-choice-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                        <h6 class="mb-1"><?= T::tr('Quick rating') ?></h6>
                        <p class="small text-body-secondary mb-2">
                            <?= T::tr('Give it a score from 1 to 10 and add a short note. Takes 30 seconds.') ?>
                        </p>
                        <ul class="review-choice-list small">
                            <li><i class="bi bi-check2 text-success"></i> <?= T::tr('One overall score (with quarter-point precision)') ?></li>
                            <li><i class="bi bi-check2 text-success"></i> <?= T::tr('Optional title & text review') ?></li>
                        </ul>
                        <span class="btn btn-primary btn-sm mt-2 w-100">
                            <?= T::tr('Quick rating') ?> <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="<?= Html::encode($detailedUrl) ?>" class="review-choice-card review-choice-card--detailed js-load-modal" data-target="#mainModal">
                        <div class="review-choice-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
                        <h6 class="mb-1"><?= T::tr('Detailed review') ?></h6>
                        <p class="small text-body-secondary mb-2">
                            <?= T::tr('Answer a few quick questions across 6 dimensions for a richer review.') ?>
                        </p>
                        <ul class="review-choice-list small">
                            <li><i class="bi bi-check2 text-success"></i> <?= T::tr('Look, build, play, quality, value, overall') ?></li>
                            <li><i class="bi bi-check2 text-success"></i> <?= T::tr('Powers the radar chart on the set page') ?></li>
                        </ul>
                        <span class="btn btn-dark btn-sm mt-2 w-100">
                            <?= T::tr('Detailed review') ?> <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </a>
                </div>
            </div>

            <?php if ($existing): ?>
                <div class="alert alert-info small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <?= T::tr('You already rated this set ({score}/10). Choosing any option will overwrite your previous review.', [
                        'score' => number_format((float)$existing->overall_score, 2, '.', ''),
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
