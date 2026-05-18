<?php

use common\components\Html;
use common\models\Set;
use common\models\SetReview;
use frontend\components\T;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var Set            $set
 * @var SetReview|null $existing
 * @var bool           $ownsSet
 */

$initialScore = $existing ? (float)$existing->overall_score : 8.0;
$initialTitle = $existing?->title ?? '';
$initialContent = $existing?->content ?? '';
$saveUrl = Url::to(['/review/save-simple']);
$detailedUrl = Url::to(['/review/default/detailed-modal', 'setId' => (int)$set->id]);
$choiceUrl = Url::to(['/review/default/choice-modal', 'setId' => (int)$set->id]);

$i18n = [
    'errorGeneric' => T::tr('An error occurred. Please try again.'),
    'errorConnection' => T::tr('Connection error. Check your internet and try again.'),
    'saving' => T::tr('Saving...'),
];
?>
<div class="modal-dialog modal-dialog-centered modal-lg review-modal-dialog">
    <div class="modal-content review-modal" data-review-i18n="<?= Html::encode(Json::encode($i18n)) ?>">
        <div class="modal-header border-0 pb-0">
            <div class="w-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="modal-title d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <?= T::tr('Quick rating') ?>
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

        <form class="review-simple-form" data-save-url="<?= Html::encode($saveUrl) ?>" data-set-id="<?= (int)$set->id ?>">
            <div class="modal-body">
                <div class="alert alert-danger d-none js-form-alert mb-3" role="alert"></div>

                <div class="review-slider-block text-center">
                    <div class="review-slider-value-wrap">
                        <span class="review-slider-value" data-role="score-value"><?= number_format($initialScore, 2, '.', '') ?></span>
                        <span class="review-slider-out-of">/ 10</span>
                    </div>
                    <div class="review-stars" data-role="score-stars" aria-hidden="true">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <span class="review-star"><i class="bi bi-star"></i></span>
                        <?php endfor; ?>
                    </div>
                    <input
                        type="range"
                        class="form-range review-slider"
                        min="1" max="10" step="0.25"
                        value="<?= number_format($initialScore, 2, '.', '') ?>"
                        data-role="score-slider"
                        aria-label="<?= Html::encode(T::tr('Overall score')) ?>"
                    >
                    <div class="d-flex justify-content-between small text-body-secondary px-1">
                        <span>1</span>
                        <span>5</span>
                        <span>10</span>
                    </div>
                </div>

                <?php if (!$ownsSet): ?>
                    <div class="form-check review-owns-check">
                        <input class="form-check-input" type="checkbox" id="reviewOwnsSet" data-role="owns-set" checked>
                        <label class="form-check-label" for="reviewOwnsSet">
                            <i class="bi bi-bookmark-check me-1"></i>
                            <?= T::tr('I own this set — add it to my collection') ?>
                        </label>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">
                        <?= T::tr('Title') ?> <span class="text-body-tertiary">(<?= T::tr('optional') ?>)</span>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        maxlength="255"
                        placeholder="<?= Html::encode(T::tr('Sum it up in one line')) ?>"
                        data-role="title"
                        value="<?= Html::encode($initialTitle) ?>"
                    >
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">
                        <?= T::tr('Review') ?> <span class="text-body-tertiary">(<?= T::tr('optional') ?>)</span>
                    </label>
                    <textarea
                        class="form-control"
                        rows="4"
                        maxlength="2000"
                        placeholder="<?= Html::encode(T::tr('Why this score? What stood out?')) ?>"
                        data-role="content"
                    ><?= Html::encode($initialContent) ?></textarea>
                </div>

                <p class="small text-body-secondary mb-0">
                    <?= T::tr('Want to give a more thorough review?') ?>
                    <a href="<?= Html::encode($detailedUrl) ?>" class="js-load-modal" data-target="#mainModal">
                        <?= T::tr('Switch to detailed review') ?>
                    </a>
                </p>
            </div>
            <div class="modal-footer">
                <a href="<?= Html::encode($choiceUrl) ?>" class="btn btn-link text-body-secondary me-auto js-load-modal" data-target="#mainModal">
                    <i class="bi bi-arrow-left me-1"></i><?= T::tr('Change rating type') ?>
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= T::tr('Cancel') ?></button>
                <button type="submit" class="btn btn-primary" data-role="submit">
                    <?= $existing ? T::tr('Update review') : T::tr('Publish review') ?>
                </button>
            </div>
        </form>
    </div>
</div>
