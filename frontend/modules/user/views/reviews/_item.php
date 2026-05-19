<?php

use common\models\Set;
use common\models\SetReview;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View                 $this
 * @var SetReview            $review
 * @var Set                  $set
 * @var array<string,string> $dimensionShortLabels
 */

$stars = SetReview::buildStarClasses((float)$review->overall_score);
$statusLabel = SetReview::getStatusLabel((int)$review->status);
$statusClass = SetReview::getStatusBadgeClass((int)$review->status);
$dateRaw = $review->published_at ?: $review->created_at;
$dateLabel = $dateRaw ? Yii::$app->formatter->asDate($dateRaw, 'medium') : '';
$setUrl = Url::to(["/lego/{$set->slug}"]);
$editUrl = Url::to(['/review/default/choice-modal', 'setId' => (int)$set->id]);
$scoresMap = $review->isDetailed() ? $review->getScoresMap() : [];

?>
<article class="my-review-row">
    <a class="my-review-thumb" href="<?= $setUrl ?>">
        <img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="<?= Html::encode((string)$set->name) ?>" loading="lazy">
    </a>
    <div class="my-review-body">
        <div class="my-review-head">
            <div class="my-review-set">
                <a class="my-review-set-name" href="<?= $setUrl ?>"><?= Html::encode((string)$set->name) ?></a>
                <span class="my-review-set-number">#<?= Html::encode((string)$set->number) ?></span>
            </div>
            <div class="my-review-head-meta">
                <span class="badge <?= Html::encode($statusClass) ?>"><?= Html::encode($statusLabel) ?></span>
                <span class="badge text-bg-light border">
                    <?= $review->isDetailed() ? T::tr('Detailed') : T::tr('Quick rating') ?>
                </span>
            </div>
        </div>

        <div class="my-review-score-row">
            <span class="my-review-score">
                <?= Html::encode(number_format((float)$review->overall_score, 2, '.', '')) ?>
                <span class="my-review-score-out">/ 10</span>
            </span>
            <span class="my-review-stars" aria-hidden="true">
                <?php foreach ($stars as $cls): ?>
                    <i class="bi <?= Html::encode($cls) ?>"></i>
                <?php endforeach; ?>
            </span>
            <?php if ($dateLabel): ?>
                <span class="text-body-secondary small">
                    <i class="bi bi-calendar3 me-1"></i><?= Html::encode($dateLabel) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($review->title): ?>
            <div class="my-review-title"><?= Html::encode($review->title) ?></div>
        <?php endif; ?>

        <?php if ($review->content): ?>
            <div class="my-review-content"><?= nl2br(Html::encode((string)$review->content)) ?></div>
        <?php endif; ?>

        <?php if ($scoresMap !== []): ?>
            <div class="my-review-dims">
                <?php foreach (SetReview::DIMENSIONS as $dimKey => $dimMeta): ?>
                    <?php if (!isset($scoresMap[$dimKey])) {
                        continue;
                    } ?>
                    <span class="my-review-dim">
                        <span class="my-review-dim-label"><?= Html::encode($dimensionShortLabels[$dimKey] ?? $dimKey) ?></span>
                        <span class="my-review-dim-value"><?= Html::encode(number_format((float)$scoresMap[$dimKey], 2, '.', '')) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="my-review-actions">
            <?= Html::a(
                    '<i class="bi bi-pencil me-1"></i>' . Html::encode(T::tr('Edit')),
                    $editUrl,
                    [
                            'class'       => 'btn btn-sm btn-outline-primary js-load-modal',
                            'data-target' => '#mainModal',
                            'encode'      => false,
                    ]
            ) ?>
            <?= Html::a(
                    '<i class="bi bi-box-arrow-up-right me-1"></i>' . Html::encode(T::tr('Open set')),
                    $setUrl . '#reviews',
                    ['class' => 'btn btn-sm btn-outline-secondary', 'encode' => false]
            ) ?>
        </div>
    </div>
</article>
