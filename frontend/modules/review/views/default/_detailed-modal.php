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
 * @var array          $preferences  ['set_purpose' => [...], 'priority' => [...]]
 */

$saveUrl = Url::to(['/review/save-detailed']);
$simpleUrl = Url::to(['/review/default/simple-modal', 'setId' => (int)$set->id]);
$choiceUrl = Url::to(['/review/default/choice-modal', 'setId' => (int)$set->id]);

$dimensionPrompts = [
    'design'           => T::tr('How much do you like how the set looks once built?'),
    'build_experience' => T::tr('How enjoyable was the building experience?'),
    'playability'      => T::tr('How would you rate the features and playability?'),
    'quality'          => T::tr('How is the quality of the elements?'),
    'value'            => T::tr('Is the set worth its price?'),
    'recommendation'   => T::tr('How strongly would you recommend this set?'),
];

$dimensionLabels = [
    'design'           => T::tr('Look & design'),
    'build_experience' => T::tr('Building experience'),
    'playability'      => T::tr('Features & play'),
    'quality'          => T::tr('Element quality'),
    'value'            => T::tr('Price-to-value'),
    'recommendation'   => T::tr('Overall impression'),
];

$dimensionEmojis = [
    'design'           => '🎨',
    'build_experience' => '🧱',
    'playability'      => '🤖',
    'quality'          => '🧩',
    'value'            => '💸',
    'recommendation'   => '🏆',
];

$questionLabels = [
    'design_theme_fit'   => T::tr('Does the set capture the theme / license well?'),
    'design_colors'      => T::tr('Are the colors and proportions visually appealing?'),
    'build_instructions' => T::tr('Were the instructions clear and logical?'),
    'build_complexity'   => T::tr('How was the build complexity?'),
    'build_techniques'   => T::tr('Did the build use any interesting techniques?'),
    'play_functions'     => T::tr('Are there moving parts or mechanisms that work well?'),
    'play_purpose'       => T::tr('Is this set better for play or for display?'),
    'play_minifigs'      => T::tr('Are the minifigures interesting and fit the set?'),
    'quality_fit'        => T::tr('Do the elements fit together well?'),
    'quality_stickers'   => T::tr('How are the stickers (if any)?'),
    'quality_unique'     => T::tr('Are there unique or rare elements in the set?'),
    'value_worth'        => T::tr('Do you think this set is worth its price?'),
    'value_pieces'       => T::tr('Is the piece count adequate for the price?'),
    'value_feel'         => T::tr('Does the set feel premium or budget?'),
    'would_buy_again'    => T::tr('Would you buy this set again?'),
    'liked_most'         => T::tr('What did you like most?'),
    'disliked_most'      => T::tr('What did you like least?'),
];

$optionLabels = [
    'design_theme_fit'   => ['poor' => T::tr('Poorly'), 'average' => T::tr('Average'), 'good' => T::tr('Well'), 'excellent' => T::tr('Excellently')],
    'design_colors'      => ['poor' => T::tr('No'), 'average' => T::tr('Average'), 'attractive' => T::tr('Yes, attractive')],
    'build_instructions' => ['confusing' => T::tr('Confusing'), 'okay' => T::tr('Okay'), 'clear' => T::tr('Clear & logical')],
    'build_complexity'   => ['too_simple' => T::tr('Too simple'), 'just_right' => T::tr('Just right'), 'too_complex' => T::tr('Too complex')],
    'build_techniques'   => ['no' => T::tr('No'), 'yes' => T::tr('Yes')],
    'play_functions'     => ['none' => T::tr('None'), 'some' => T::tr('Some'), 'great' => T::tr('Yes, work great')],
    'play_purpose'       => ['play' => T::tr('Play'), 'display' => T::tr('Display'), 'both' => T::tr('Both')],
    'play_minifigs'      => ['na' => T::tr('No minifigures'), 'poor' => T::tr('Boring'), 'average' => T::tr('Okay'), 'great' => T::tr('Great fit')],
    'quality_fit'        => ['poor' => T::tr('Poor'), 'average' => T::tr('Average'), 'good' => T::tr('Good')],
    'quality_stickers'   => ['na' => T::tr('No stickers'), 'poor' => T::tr('Hard to apply'), 'good' => T::tr('Good quality')],
    'quality_unique'     => ['no' => T::tr('No'), 'yes' => T::tr('Yes')],
    'value_worth'        => ['no' => T::tr('No'), 'average' => T::tr('Somewhat'), 'yes' => T::tr('Yes')],
    'value_pieces'       => ['too_few' => T::tr('Too few'), 'just_right' => T::tr('Just right'), 'lots' => T::tr('Plenty')],
    'value_feel'         => ['budget' => T::tr('Budget'), 'standard' => T::tr('Standard'), 'premium' => T::tr('Premium')],
    'would_buy_again'    => ['no' => T::tr('No'), 'maybe' => T::tr('Maybe'), 'yes' => T::tr('Yes')],
];

$preferenceLabels = [
    'set_purpose' => T::tr('I prefer sets that are mainly for...'),
    'priority'    => T::tr('What matters most to you in a set? (up to 2)'),
];

$preferenceOptionLabels = [
    'set_purpose' => [
        'display'    => ['label' => T::tr('Display'), 'emoji' => '🏆'],
        'play'       => ['label' => T::tr('Play'), 'emoji' => '🎮'],
        'collection' => ['label' => T::tr('Collection'), 'emoji' => '⭐'],
        'technical'  => ['label' => T::tr('Technical'), 'emoji' => '⚙️'],
        'minifigs'   => ['label' => T::tr('Minifigures'), 'emoji' => '🧑'],
    ],
    'priority' => [
        'pieces'      => ['label' => T::tr('Piece count'), 'emoji' => '🧱'],
        'playability' => ['label' => T::tr('Playability'), 'emoji' => '🎮'],
        'looks'       => ['label' => T::tr('Looks'), 'emoji' => '🎨'],
        'price'       => ['label' => T::tr('Price'), 'emoji' => '💸'],
        'license'     => ['label' => T::tr('License / IP'), 'emoji' => '🎬'],
    ],
];

$existingScores = $existing ? $existing->getScoresMap() : [];
$existingAnswers = $existing ? $existing->getAnswersMap() : [];

$showOwnsStep = !$ownsSet;

$i18n = [
    'errorGeneric' => T::tr('An error occurred. Please try again.'),
    'errorConnection' => T::tr('Connection error. Check your internet and try again.'),
    'saving' => T::tr('Saving...'),
    'stepOf' => T::tr('Step {current} of {total}', ['current' => '{current}', 'total' => '{total}']),
];

$stepTitles = [
    'owns'           => T::tr('Do you own this set?'),
    'design'         => $dimensionLabels['design'],
    'build_experience' => $dimensionLabels['build_experience'],
    'playability'    => $dimensionLabels['playability'],
    'quality'        => $dimensionLabels['quality'],
    'value'          => $dimensionLabels['value'],
    'recommendation' => $dimensionLabels['recommendation'],
    'preferences'    => T::tr('A few words about your taste'),
    'summary'        => T::tr('Add a summary'),
];

$renderQuestion = static function (array $question) use ($questionLabels, $optionLabels, $existingAnswers): string {
    $key = $question['key'];
    $label = $questionLabels[$key] ?? $key;
    $existingValue = $existingAnswers[$key] ?? null;
    $existingValue = is_array($existingValue) ? null : $existingValue;

    if ($question['type'] === 'text') {
        $value = is_string($existingValue) ? $existingValue : '';
        return '<div class="review-q review-q--text">'
            . '<div class="review-q-label">' . Html::encode($label) . '</div>'
            . '<textarea class="form-control" rows="2" maxlength="500" data-role="answer" data-question-key="' . Html::encode($key) . '">' . Html::encode($value) . '</textarea>'
            . '</div>';
    }

    $options = $question['options'] ?? [];
    $html = '<div class="review-q">';
    $html .= '<div class="review-q-label">' . Html::encode($label) . '</div>';
    $html .= '<div class="review-q-options">';
    foreach ($options as $optValue) {
        $optLabel = $optionLabels[$key][$optValue] ?? $optValue;
        $isSelected = ($existingValue === $optValue);
        $cls = 'review-q-option' . ($isSelected ? ' review-q-option--selected' : '');
        $html .= '<button type="button" class="' . $cls . '" data-role="answer-option" data-question-key="' . Html::encode($key) . '" data-value="' . Html::encode($optValue) . '">'
            . Html::encode($optLabel)
            . '</button>';
    }
    $html .= '</div></div>';
    return $html;
};

$renderSlider = static function (string $dimensionKey, float $initialScore, string $prompt): string {
    $value = number_format($initialScore, 2, '.', '');
    $html  = '<div class="review-slider-block review-slider-block--compact" data-role="dimension-slider" data-dimension="' . Html::encode($dimensionKey) . '">';
    $html .= '<p class="review-dim-prompt small text-body-secondary mb-2">' . Html::encode($prompt) . '</p>';
    $html .= '<div class="review-slider-value-wrap">';
    $html .= '<span class="review-slider-value" data-role="score-value">' . $value . '</span>';
    $html .= '<span class="review-slider-out-of">/ 10</span>';
    $html .= '</div>';
    $html .= '<div class="review-stars" data-role="score-stars" aria-hidden="true">';
    for ($i = 0; $i < 5; $i++) {
        $html .= '<span class="review-star"><i class="bi bi-star"></i></span>';
    }
    $html .= '</div>';
    $html .= '<input type="range" class="form-range review-slider" min="1" max="10" step="0.25" value="' . $value . '" data-role="score-slider" data-dimension="' . Html::encode($dimensionKey) . '">';
    $html .= '<div class="d-flex justify-content-between small text-body-secondary px-1"><span>1</span><span>5</span><span>10</span></div>';
    $html .= '</div>';
    return $html;
};

$defaultDimensionScore = 7.5;

// Build the ordered list of steps
$steps = [];
if ($showOwnsStep) {
    $steps[] = 'owns';
}
foreach (array_keys(SetReview::DIMENSIONS) as $dimKey) {
    $steps[] = $dimKey;
}
$steps[] = 'preferences';
$steps[] = 'summary';
$totalSteps = count($steps);
?>
<div class="modal-dialog modal-dialog-centered modal-lg review-modal-dialog review-modal-dialog--wizard">
    <div class="modal-content review-modal review-wizard"
         data-review-i18n="<?= Html::encode(Json::encode($i18n)) ?>"
         data-save-url="<?= Html::encode($saveUrl) ?>"
         data-set-id="<?= (int)$set->id ?>"
         data-total-steps="<?= $totalSteps ?>">

        <div class="modal-header review-wizard-header border-0 pb-0">
            <div class="w-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="modal-title d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-bar-chart-line-fill text-primary"></i>
                            <span data-role="step-title"><?= Html::encode($stepTitles[$steps[0]] ?? '') ?></span>
                        </h5>
                        <div class="small text-body-secondary">
                            <?= Html::encode($set->name) ?>
                            <?php if ($set->getSetNumberText('') !== ''): ?>
                                · #<?= Html::encode($set->getSetNumberText()) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="small text-body-secondary" data-role="step-indicator"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Html::encode(T::tr('Close')) ?>"></button>
                    </div>
                </div>
                <div class="review-wizard-progress" data-role="progress">
                    <div class="review-wizard-progress-bar" data-role="progress-bar"></div>
                </div>
            </div>
        </div>

        <div class="modal-body review-wizard-body">
            <div class="alert alert-danger d-none js-form-alert mb-3" role="alert"></div>

            <?php if ($showOwnsStep): ?>
                <div class="review-step" data-step="owns">
                    <p class="text-body-secondary mb-3">
                        <?= T::tr('A more accurate review comes from someone who actually built it. If you own this set we can add it to your collection in one click.') ?>
                    </p>
                    <div class="review-owns-grid">
                        <button type="button" class="review-owns-card review-owns-card--yes" data-role="owns-choice" data-value="yes">
                            <div class="review-owns-emoji">🧱</div>
                            <div class="fw-semibold"><?= T::tr('Yes, I built it') ?></div>
                            <div class="small text-body-secondary"><?= T::tr('Add to my collection') ?></div>
                        </button>
                        <button type="button" class="review-owns-card review-owns-card--no" data-role="owns-choice" data-value="no">
                            <div class="review-owns-emoji">👀</div>
                            <div class="fw-semibold"><?= T::tr('No, just rating it') ?></div>
                            <div class="small text-body-secondary"><?= T::tr('I have an opinion anyway') ?></div>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach (SetReview::DIMENSIONS as $dimKey => $dimMeta): ?>
                <?php
                $initialScore = isset($existingScores[$dimKey])
                    ? (float)$existingScores[$dimKey]
                    : $defaultDimensionScore;
                ?>
                <div class="review-step" data-step="<?= Html::encode($dimKey) ?>" hidden>
                    <div class="review-dim-emoji"><?= $dimensionEmojis[$dimKey] ?></div>
                    <?= $renderSlider($dimKey, $initialScore, $dimensionPrompts[$dimKey]) ?>

                    <?php if (!empty(SetReview::QUESTIONS_BY_DIMENSION[$dimKey])): ?>
                        <div class="review-questions">
                            <?php foreach (SetReview::QUESTIONS_BY_DIMENSION[$dimKey] as $question): ?>
                                <?= $renderQuestion($question) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="review-step" data-step="preferences" hidden>
                <p class="text-body-secondary mb-3">
                    <?= T::tr('A few quick taste questions — they help us tailor recommendations across all your reviews. Totally optional.') ?>
                </p>
                <?php foreach (SetReview::PREFERENCE_QUESTIONS as $prefQ): ?>
                    <?php
                    $prefKey = $prefQ['key'];
                    $maxSelect = $prefQ['max_select'] ?? null;
                    $selectedValues = is_array($preferences[$prefKey] ?? null) ? $preferences[$prefKey] : [];
                    ?>
                    <div class="review-pref-block" data-pref-key="<?= Html::encode($prefKey) ?>" data-max-select="<?= $maxSelect ? (int)$maxSelect : '0' ?>">
                        <div class="review-q-label mb-2"><?= Html::encode($preferenceLabels[$prefKey] ?? $prefKey) ?></div>
                        <div class="review-pref-options">
                            <?php foreach ($prefQ['options'] as $optValue): ?>
                                <?php
                                $meta = $preferenceOptionLabels[$prefKey][$optValue] ?? ['label' => $optValue, 'emoji' => ''];
                                $isSelected = in_array($optValue, $selectedValues, true);
                                ?>
                                <button type="button"
                                        class="review-pref-option<?= $isSelected ? ' review-pref-option--selected' : '' ?>"
                                        data-role="pref-option"
                                        data-pref-key="<?= Html::encode($prefKey) ?>"
                                        data-value="<?= Html::encode($optValue) ?>">
                                    <span class="review-pref-emoji"><?= $meta['emoji'] ?></span>
                                    <span><?= Html::encode($meta['label']) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="review-step" data-step="summary" hidden>
                <div class="review-summary-overall mb-4 text-center">
                    <div class="small text-body-secondary text-uppercase fw-semibold mb-1"><?= T::tr('Your overall score') ?></div>
                    <div class="review-summary-score-value">
                        <span data-role="overall-display"><?= number_format($defaultDimensionScore, 2, '.', '') ?></span>
                        <span class="review-summary-out">/ 10</span>
                    </div>
                    <div class="review-stars" data-role="overall-stars" aria-hidden="true">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <span class="review-star"><i class="bi bi-star"></i></span>
                        <?php endfor; ?>
                    </div>
                    <p class="small text-body-secondary mt-2 mb-0">
                        <?= T::tr('Average of your six dimension scores. You can still tweak any one by going back.') ?>
                    </p>
                </div>

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
                        value="<?= Html::encode($existing?->title ?? '') ?>"
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
                        placeholder="<?= Html::encode(T::tr('Anything else worth mentioning?')) ?>"
                        data-role="content"
                    ><?= Html::encode($existing?->content ?? '') ?></textarea>
                </div>
            </div>

            <input type="hidden" data-role="owns-set" value="<?= $ownsSet ? '1' : '0' ?>">
            <input type="hidden" data-role="step-titles" value="<?= Html::encode(Json::encode($stepTitles)) ?>">
            <input type="hidden" data-role="steps-order" value="<?= Html::encode(Json::encode($steps)) ?>">
        </div>

        <div class="modal-footer review-wizard-footer">
            <a href="<?= Html::encode($choiceUrl) ?>" class="btn btn-link text-body-secondary me-auto js-load-modal" data-target="#mainModal">
                <i class="bi bi-arrow-left me-1"></i><?= T::tr('Change rating type') ?>
            </a>
            <button type="button" class="btn btn-outline-secondary" data-role="back" hidden>
                <i class="bi bi-arrow-left me-1"></i><?= T::tr('Back') ?>
            </button>
            <a href="<?= Html::encode($simpleUrl) ?>" class="btn btn-outline-secondary js-load-modal" data-target="#mainModal" data-role="switch-simple">
                <?= T::tr('Switch to quick rating') ?>
            </a>
            <button type="button" class="btn btn-primary" data-role="next">
                <?= T::tr('Next') ?><i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-success" data-role="submit" hidden>
                <i class="bi bi-check-lg me-1"></i><?= $existing ? T::tr('Update review') : T::tr('Publish review') ?>
            </button>
        </div>
    </div>
</div>
