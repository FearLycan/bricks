<?php

use common\components\Html;
use common\models\Set;
use common\models\SetReview;
use frontend\components\T;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var Set                $set
 * @var array              $stats   from SetReview::getSetStats()
 * @var SetReview[]        $reviews most recent reviews
 * @var SetReview|null     $userReview
 */

$reviewCount = (int)($stats['review_count'] ?? 0);
$average = $stats['average'] ?? null;
$distribution = is_array($stats['distribution'] ?? null) ? $stats['distribution'] : [];
$dimensions = is_array($stats['dimensions'] ?? null) ? $stats['dimensions'] : [];

$dimensionOrder = array_keys(SetReview::DIMENSIONS);
$dimensionLabels = [
    'design'           => T::tr('Look'),
    'build_experience' => T::tr('Build'),
    'playability'      => T::tr('Features'),
    'quality'          => T::tr('Quality'),
    'value'            => T::tr('Value'),
    'recommendation'   => T::tr('Overall'),
];

$radarLabels = [];
$radarValues = [];
foreach ($dimensionOrder as $dimKey) {
    $radarLabels[] = $dimensionLabels[$dimKey] ?? $dimKey;
    $radarValues[] = isset($dimensions[$dimKey]['avg']) ? round((float)$dimensions[$dimKey]['avg'], 2) : 0;
}

$histogramLabels = [];
$histogramValues = [];
for ($bucket = 1; $bucket <= 10; $bucket++) {
    $histogramLabels[] = (string)$bucket;
    $histogramValues[] = (int)($distribution[$bucket] ?? 0);
}
$histogramMax = $histogramValues === [] ? 0 : max($histogramValues);

$choiceUrl = Url::to(['/review/default/choice-modal', 'setId' => (int)$set->id]);
$ctaLabel = $userReview ? T::tr('Update your review') : T::tr('Rate this set');

$answerAggregates = is_array($stats['answers'] ?? null) ? $stats['answers'] : [];
$textAnswers = is_array($stats['text_answers'] ?? null) ? $stats['text_answers'] : [];

/**
 * Ordered list of question keys we want to display in the aggregate section,
 * grouped by dimension to match the rest of the panel.
 */
$displayQuestionOrder = [
    'design_theme_fit', 'design_colors',
    'build_instructions', 'build_complexity', 'build_techniques',
    'play_functions', 'play_purpose', 'play_minifigs',
    'quality_fit', 'quality_stickers', 'quality_unique',
    'value_worth', 'value_pieces', 'value_feel',
    'would_buy_again',
    'set_purpose', 'priority',
];

$buildStars = static function (?float $score): array {
    if ($score === null) {
        return array_fill(0, 5, 'bi-star');
    }
    $value = max(0.0, min(10.0, (float)$score)) / 2.0;
    $classes = [];
    for ($i = 1; $i <= 5; $i++) {
        if ($value >= $i) {
            $classes[] = 'bi-star-fill';
        } elseif ($value >= $i - 0.5) {
            $classes[] = 'bi-star-half';
        } else {
            $classes[] = 'bi-star';
        }
    }
    return $classes;
};

$starClasses = $buildStars($average !== null ? (float)$average : null);

$radarData = Json::encode([
    'labels' => $radarLabels,
    'values' => $radarValues,
]);
$histogramData = Json::encode([
    'labels' => $histogramLabels,
    'values' => $histogramValues,
]);

$reviewListId = 'review-list-' . (int)$set->id;
?>
<div class="review-stats">
    <?php if ($reviewCount === 0): ?>
        <div class="review-empty-state text-center">
            <div class="review-empty-emoji">🌟</div>
            <h6 class="mb-2"><?= T::tr('No reviews yet — be the first!') ?></h6>
            <p class="text-body-secondary small mb-3">
                <?= T::tr('Your opinion helps other builders pick the perfect set.') ?>
            </p>
            <?= Html::a('<i class="bi bi-stars me-1"></i>' . Html::encode($ctaLabel), $choiceUrl, [
                'class'       => 'btn btn-primary js-load-modal',
                'data-target' => '#mainModal',
            ]) ?>
        </div>
    <?php else: ?>
        <div class="row g-4 review-stats-top">
            <div class="col-lg-4">
                <div class="review-overall-card">
                    <div class="review-overall-score">
                        <?= Html::encode($average !== null ? number_format((float)$average, 2, '.', '') : '0.00') ?>
                        <span class="review-overall-out">/ 10</span>
                    </div>
                    <div class="review-stars review-stars--lg" aria-hidden="true">
                        <?php foreach ($starClasses as $cls): ?>
                            <i class="bi <?= Html::encode($cls) ?>"></i>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-body-secondary small mb-3">
                        <?= T::tr('Based on {n, plural, =1{# review} other{# reviews}}', ['n' => $reviewCount]) ?>
                    </div>
                    <?= Html::a('<i class="bi bi-stars me-1"></i>' . Html::encode($ctaLabel), $choiceUrl, [
                        'class'       => 'btn btn-primary w-100 js-load-modal',
                        'data-target' => '#mainModal',
                    ]) ?>

                    <?php if ($userReview): ?>
                        <div class="review-your-summary mt-3">
                            <div class="small text-body-secondary mb-1">
                                <i class="bi bi-person-check me-1"></i><?= T::tr('Your review') ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge text-bg-warning text-dark">
                                    <i class="bi bi-star-fill me-1"></i><?= Html::encode(number_format((float)$userReview->overall_score, 2, '.', '')) ?>
                                </span>
                                <span class="small text-body-secondary">
                                    <?= $userReview->isDetailed() ? T::tr('Detailed') : T::tr('Quick') ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="review-chart-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold small"><?= T::tr('Score by dimension') ?></span>
                        <span class="small text-body-secondary"><?= T::tr('avg 0–10') ?></span>
                    </div>
                    <?php if (array_sum($radarValues) > 0): ?>
                        <div class="review-radar-wrap">
                            <canvas data-role="radar-chart" data-chart="<?= Html::encode($radarData) ?>"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="review-radar-empty small text-body-secondary text-center py-4">
                            <i class="bi bi-bar-chart-line"></i>
                            <div><?= T::tr('Detailed reviews unlock the radar chart.') ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="review-chart-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold small"><?= T::tr('Score distribution') ?></span>
                        <span class="small text-body-secondary"><?= T::tr('reviews per bucket') ?></span>
                    </div>
                    <div class="review-histogram">
                        <?php for ($bucket = 1; $bucket <= 10; $bucket++): ?>
                            <?php
                            $count = (int)($distribution[$bucket] ?? 0);
                            $heightPct = $histogramMax > 0 ? (int)round(($count / $histogramMax) * 100) : 0;
                            ?>
                            <div class="review-histogram-col" title="<?= Html::encode($bucket) ?>: <?= Html::encode((string)$count) ?>">
                                <div class="review-histogram-fill" style="--h: <?= (int)$heightPct ?>%">
                                    <?php if ($count > 0): ?>
                                        <span class="review-histogram-count"><?= Html::encode((string)$count) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="review-histogram-label"><?= Html::encode((string)$bucket) ?></div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($dimensions)): ?>
            <div class="review-dim-bars mt-4">
                <h6 class="mb-3"><?= T::tr('Average per dimension') ?></h6>
                <div class="row g-3">
                    <?php foreach ($dimensionOrder as $dimKey): ?>
                        <?php
                        $avg = isset($dimensions[$dimKey]['avg']) ? (float)$dimensions[$dimKey]['avg'] : null;
                        $count = isset($dimensions[$dimKey]['count']) ? (int)$dimensions[$dimKey]['count'] : 0;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="review-dim-row">
                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                    <span class="small fw-semibold"><?= Html::encode($dimensionLabels[$dimKey] ?? $dimKey) ?></span>
                                    <span class="small text-body-secondary">
                                        <?= $avg !== null ? Html::encode(number_format($avg, 2, '.', '')) . ' / 10' : '—' ?>
                                        <span class="ms-1 text-body-tertiary">(<?= (int)$count ?>)</span>
                                    </span>
                                </div>
                                <div class="review-dim-bar">
                                    <div class="review-dim-bar-fill" style="--w: <?= $avg !== null ? (int)round($avg * 10) : 0 ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $aggregatesToShow = array_values(array_filter(
            $displayQuestionOrder,
            static fn($k) => isset($answerAggregates[$k]) && ($answerAggregates[$k]['total'] ?? 0) > 0
        ));
        ?>
        <?php if ($aggregatesToShow !== []): ?>
            <?php $aggregateCollapseId = 'review-answers-aggregate-' . (int)$set->id; ?>
            <div class="review-answers-aggregate mt-4">
                <button class="review-collapse-toggle collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#<?= Html::encode($aggregateCollapseId) ?>"
                        aria-expanded="false" aria-controls="<?= Html::encode($aggregateCollapseId) ?>">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-square-text"></i>
                        <span class="fw-semibold"><?= T::tr('What reviewers say') ?></span>
                        <span class="badge text-bg-light border ms-1"><?= T::tr('{n, plural, =1{# question} other{# questions}}', ['n' => count($aggregatesToShow)]) ?></span>
                    </span>
                    <i class="bi bi-chevron-down review-collapse-chevron"></i>
                </button>
                <div class="collapse" id="<?= Html::encode($aggregateCollapseId) ?>">
                    <div class="row g-3 pt-3">
                        <?php foreach ($aggregatesToShow as $qKey): ?>
                            <?php
                            $aggregate = $answerAggregates[$qKey];
                            $total = (int)$aggregate['total'];
                            $counts = $aggregate['counts'];
                            arsort($counts, SORT_NUMERIC);
                            ?>
                            <div class="col-md-6">
                                <div class="review-answer-q">
                                    <div class="review-answer-q-label"><?= Html::encode(SetReview::getQuestionLabel($qKey)) ?></div>
                                    <div class="review-answer-q-bars">
                                        <?php foreach ($counts as $value => $count): ?>
                                            <?php
                                            $pct = $total > 0 ? (int)round(($count / $total) * 100) : 0;
                                            $isPositive = SetReview::isPositiveAnswer($qKey, (string)$value);
                                            ?>
                                            <div class="review-answer-q-row<?= $isPositive ? ' review-answer-q-row--positive' : '' ?>">
                                                <span class="review-answer-q-text"><?= Html::encode(SetReview::getAnswerLabel($qKey, (string)$value)) ?></span>
                                                <div class="review-answer-q-bar">
                                                    <div class="review-answer-q-bar-fill" style="--w: <?= (int)$pct ?>%"></div>
                                                </div>
                                                <span class="review-answer-q-pct"><?= (int)$pct ?>%</span>
                                                <span class="review-answer-q-cnt">(<?= (int)$count ?>)</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php $hasHighlights = !empty($textAnswers['liked_most']) || !empty($textAnswers['disliked_most']); ?>
        <?php if ($hasHighlights): ?>
            <div class="review-highlights mt-4">
                <div class="row g-3">
                    <?php if (!empty($textAnswers['liked_most'])): ?>
                        <div class="col-md-6">
                            <div class="review-highlight-card review-highlight-card--positive">
                                <div class="review-highlight-header">
                                    <i class="bi bi-hand-thumbs-up-fill"></i>
                                    <span><?= T::tr('What people loved') ?></span>
                                </div>
                                <ul class="review-highlight-list">
                                    <?php foreach ($textAnswers['liked_most'] as $item): ?>
                                        <li>
                                            <span class="review-highlight-quote">“<?= Html::encode($item['text']) ?>”</span>
                                            <?php if ($item['author']): ?>
                                                <span class="review-highlight-author">— <?= Html::encode($item['author']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($textAnswers['disliked_most'])): ?>
                        <div class="col-md-6">
                            <div class="review-highlight-card review-highlight-card--negative">
                                <div class="review-highlight-header">
                                    <i class="bi bi-hand-thumbs-down-fill"></i>
                                    <span><?= T::tr('What could be better') ?></span>
                                </div>
                                <ul class="review-highlight-list">
                                    <?php foreach ($textAnswers['disliked_most'] as $item): ?>
                                        <li>
                                            <span class="review-highlight-quote">“<?= Html::encode($item['text']) ?>”</span>
                                            <?php if ($item['author']): ?>
                                                <span class="review-highlight-author">— <?= Html::encode($item['author']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="review-list mt-4" id="<?= Html::encode($reviewListId) ?>">
            <h6 class="mb-3"><?= T::tr('Recent reviews') ?></h6>
            <?php if (!$reviews): ?>
                <p class="text-body-secondary small mb-0"><?= T::tr('No reviews available.') ?></p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reviews as $review): ?>
                        <?php
                        $reviewStars = $buildStars((float)$review->overall_score);
                        $authorName = $review->user->username ?? T::tr('Anonymous');
                        $reviewDate = $review->published_at ?: $review->created_at;
                        ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="review-avatar"><?= Html::encode(mb_strtoupper(mb_substr($authorName, 0, 1))) ?></span>
                                    <div>
                                        <div class="fw-semibold small"><?= Html::encode($authorName) ?></div>
                                        <div class="small text-body-secondary">
                                            <?= Html::encode(date('Y-m-d', strtotime((string)$reviewDate))) ?>
                                            ·
                                            <?= $review->isDetailed() ? T::tr('Detailed review') : T::tr('Quick rating') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-warning">
                                        <?php foreach ($reviewStars as $cls): ?>
                                            <i class="bi <?= Html::encode($cls) ?>"></i>
                                        <?php endforeach; ?>
                                    </span>
                                    <span class="badge text-bg-warning text-dark">
                                        <?= Html::encode(number_format((float)$review->overall_score, 2, '.', '')) ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($review->title): ?>
                                <div class="fw-semibold mb-1"><?= Html::encode($review->title) ?></div>
                            <?php endif; ?>

                            <?php if ($review->content): ?>
                                <div class="small text-body-secondary mb-2"><?= nl2br(Html::encode((string)$review->content)) ?></div>
                            <?php endif; ?>

                            <?php if ($review->isDetailed()): ?>
                                <?php
                                $reviewAnswersMap = $review->getAnswersMap();
                                $likedText = is_string($reviewAnswersMap['liked_most'] ?? null) ? $reviewAnswersMap['liked_most'] : null;
                                $dislikedText = is_string($reviewAnswersMap['disliked_most'] ?? null) ? $reviewAnswersMap['disliked_most'] : null;
                                ?>

                                <?php if ($likedText || $dislikedText): ?>
                                    <div class="review-item-pros-cons">
                                        <?php if ($likedText): ?>
                                            <div class="review-item-pros">
                                                <i class="bi bi-plus-circle-fill text-success"></i>
                                                <span><?= nl2br(Html::encode($likedText)) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($dislikedText): ?>
                                            <div class="review-item-cons">
                                                <i class="bi bi-dash-circle-fill text-danger"></i>
                                                <span><?= nl2br(Html::encode($dislikedText)) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $radioAnswers = [];
                                foreach ($displayQuestionOrder as $qKey) {
                                    if (in_array($qKey, ['liked_most', 'disliked_most'], true)) {
                                        continue;
                                    }
                                    $val = $reviewAnswersMap[$qKey] ?? null;
                                    if (is_array($val) && $val !== []) {
                                        $radioAnswers[$qKey] = array_values($val);
                                    } elseif (is_string($val) && $val !== '') {
                                        $radioAnswers[$qKey] = $val;
                                    }
                                }
                                ?>
                                <?php if ($radioAnswers !== []): ?>
                                    <?php $reviewAnswersCollapseId = 'review-item-answers-' . (int)$review->id; ?>
                                    <div class="review-item-answers-wrap">
                                        <button class="review-collapse-toggle review-collapse-toggle--inline collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#<?= Html::encode($reviewAnswersCollapseId) ?>"
                                                aria-expanded="false" aria-controls="<?= Html::encode($reviewAnswersCollapseId) ?>">
                                            <span class="d-flex align-items-center gap-2">
                                                <i class="bi bi-list-check"></i>
                                                <span class="small fw-semibold"><?= T::tr('Detailed answers') ?></span>
                                                <span class="badge text-bg-light border ms-1"><?= count($radioAnswers) ?></span>
                                            </span>
                                            <i class="bi bi-chevron-down review-collapse-chevron"></i>
                                        </button>
                                        <div class="collapse" id="<?= Html::encode($reviewAnswersCollapseId) ?>">
                                            <div class="review-item-answers pt-2">
                                                <?php foreach ($radioAnswers as $qKey => $value): ?>
                                                    <?php
                                                    if (is_array($value)) {
                                                        $valueLabel = implode(', ', array_map(
                                                            static fn($v) => SetReview::getAnswerLabel($qKey, (string)$v),
                                                            $value
                                                        ));
                                                        $positive = false;
                                                    } else {
                                                        $valueLabel = SetReview::getAnswerLabel($qKey, (string)$value);
                                                        $positive = SetReview::isPositiveAnswer($qKey, (string)$value);
                                                    }
                                                    ?>
                                                    <span class="review-item-answer<?= $positive ? ' review-item-answer--positive' : '' ?>"
                                                          title="<?= Html::encode(SetReview::getQuestionLabel($qKey)) ?>">
                                                        <span class="review-item-answer-label"><?= Html::encode(SetReview::getQuestionLabel($qKey)) ?>:</span>
                                                        <span class="review-item-answer-value"><?= Html::encode($valueLabel) ?></span>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php $reviewScores = $review->getScoresMap(); ?>
                                <?php if ($reviewScores): ?>
                                    <div class="review-item-dimensions">
                                        <?php foreach ($dimensionOrder as $dimKey): ?>
                                            <?php if (isset($reviewScores[$dimKey])): ?>
                                                <span class="review-item-dim">
                                                    <span class="review-item-dim-label"><?= Html::encode($dimensionLabels[$dimKey] ?? $dimKey) ?></span>
                                                    <span class="review-item-dim-score"><?= Html::encode(number_format((float)$reviewScores[$dimKey], 2, '.', '')) ?></span>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script type="application/json" data-role="review-histogram"><?= $histogramData ?></script>
</div>
