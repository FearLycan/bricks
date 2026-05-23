<?php

use common\models\Set;
use common\models\SetReview;
use common\models\User;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View                  $this
 * @var User                  $user
 * @var int                   $ownedCount
 * @var int                   $wishlistCount
 * @var int                   $reviewCount
 * @var array                 $reviewProfile         SetReview::getUserReviewProfile()
 * @var array                 $communityDimensions   dimension_key => avg
 * @var array                 $recommendedSets       [['set' => Set, 'peer_count' => int], ...]
 * @var array<string,string>  $dimensionShortLabels
 * @var string                $radarChartJson        encoded chart payload
 * @var bool                  $hasRadarData
 * @var array{dimension:string,delta:float}|null $takeawayStrictest
 * @var array{dimension:string,delta:float}|null $takeawayGenerous
 */

$this->title = T::tr('Profile') . ' · ' . $user->username;
$this->params['metaDescription'] = T::tr('Your BrickAtlas profile.');
$this->params['robots'] = 'noindex,nofollow';
$this->params['breadcrumbs'][] = T::tr('Profile');

$createdAt = new DateTimeImmutable((string)$user->created_at, new DateTimeZone(Yii::$app->timeZone));
$now = new DateTimeImmutable('now', new DateTimeZone(Yii::$app->timeZone));
$diff = $now->diff($createdAt);

$years = (int)$diff->y;
$months = (int)$diff->m;
$days = (int)$diff->d;
$totalDays = (int)$diff->days;

$initial = strtoupper(mb_substr((string)$user->username, 0, 1)) ?: '?';
$memberNumber = str_pad((string)$user->id, 5, '0', STR_PAD_LEFT);
$joinedFormatted = Yii::$app->formatter->asDate($createdAt->format('Y-m-d'), 'long');

?>

<div class="col-12 mt-4">
    <div class="user-page-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div class="user-page-header-text">
            <h1 class="page-title mb-1">
                <i class="bi bi-person-vcard-fill text-primary me-2"></i>
                <?= Html::encode(T::tr('My profile')) ?>
            </h1>
            <p class="text-muted mb-0"><?= Html::encode(T::tr('A quick look at your account on BrickAtlas.')) ?></p>
        </div>
        <?= Html::a(
            '<i class="bi bi-sliders me-2"></i>' . T::tr('Settings'),
            ['/user/settings'],
            ['class' => 'btn btn-primary', 'encode' => false]
        ) ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <section class="user-page-card mb-3">
                <div class="user-page-summary">
                    <div class="user-page-avatar"><?= Html::encode($initial) ?></div>
                    <div class="user-page-summary-body">
                        <h2 class="user-page-username">@<?= Html::encode((string)$user->username) ?></h2>
                        <p class="user-page-email"><?= Html::encode((string)$user->email) ?></p>
                        <div class="user-page-pills">
                            <span class="user-page-pill user-page-pill--member">
                                <i class="bi bi-hash"></i><?= Html::encode($memberNumber) ?>
                            </span>
                            <?php if ($user->isActive()): ?>
                                <span class="user-page-pill user-page-pill--active">
                                    <i class="bi bi-check-circle-fill"></i><?= Html::encode($user->getStatusLabel()) ?>
                                </span>
                            <?php else: ?>
                                <span class="user-page-pill">
                                    <i class="bi bi-circle"></i><?= Html::encode($user->getStatusLabel()) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($user->isAdmin()): ?>
                                <span class="user-page-pill user-page-pill--admin">
                                    <i class="bi bi-shield-fill"></i><?= Html::encode($user->getRoleLabel()) ?>
                                </span>
                            <?php else: ?>
                                <span class="user-page-pill">
                                    <i class="bi bi-person"></i><?= Html::encode($user->getRoleLabel()) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="user-page-card">
                <div class="d-flex justify-content-between align-items-baseline mb-3">
                    <h3 class="user-page-card-title">
                        <i class="bi bi-info-circle-fill"></i><?= Html::encode(T::tr('Account details')) ?>
                    </h3>
                    <span class="user-page-eyebrow"><?= Html::encode(T::tr('Identity')) ?></span>
                </div>
                <dl class="user-page-data">
                    <dt><?= Html::encode(T::tr('Username')) ?></dt>
                    <dd>@<?= Html::encode((string)$user->username) ?></dd>

                    <dt><?= Html::encode(T::tr('Email')) ?></dt>
                    <dd><?= Html::encode((string)$user->email) ?></dd>

                    <dt><?= Html::encode(T::tr('Member ID')) ?></dt>
                    <dd>#<?= Html::encode($memberNumber) ?></dd>

                    <dt><?= Html::encode(T::tr('Role')) ?></dt>
                    <dd><?= Html::encode($user->getRoleLabel()) ?></dd>

                    <dt><?= Html::encode(T::tr('Status')) ?></dt>
                    <dd><?= Html::encode($user->getStatusLabel()) ?></dd>
                </dl>
            </section>
        </div>

        <div class="col-lg-5 user-page-right-col">
            <section class="user-page-card mb-3">
                <div class="d-flex justify-content-between align-items-baseline mb-3">
                    <h3 class="user-page-card-title">
                        <i class="bi bi-calendar-event-fill"></i><?= Html::encode(T::tr('Membership tenure')) ?>
                    </h3>
                    <span class="user-page-eyebrow"><?= Html::encode(T::tr('Since')) ?> <?= Html::encode($joinedFormatted) ?></span>
                </div>

                <div class="user-page-tenure">
                    <div class="user-page-tenure-cell">
                        <span class="user-page-tenure-num"><?= (int)$years ?></span>
                        <span class="user-page-tenure-label"><?= Html::encode(T::tr($years === 1 ? 'year' : 'years')) ?></span>
                    </div>
                    <div class="user-page-tenure-cell">
                        <span class="user-page-tenure-num"><?= (int)$months ?></span>
                        <span class="user-page-tenure-label"><?= Html::encode(T::tr($months === 1 ? 'month' : 'months')) ?></span>
                    </div>
                    <div class="user-page-tenure-cell">
                        <span class="user-page-tenure-num"><?= (int)$days ?></span>
                        <span class="user-page-tenure-label"><?= Html::encode(T::tr($days === 1 ? 'day' : 'days')) ?></span>
                    </div>
                </div>

                <p class="user-page-tenure-foot">
                    <?= Html::encode(T::tr('Total of ')) ?><strong><?= Html::encode($totalDays) ?> <?= Html::encode(T::tr('days')) ?></strong><?= Html::encode(T::tr(' with us.')) ?>
                </p>
            </section>

            <div class="user-page-stats-row">
                <a class="user-page-stat-tile user-page-stat-tile--compact" href="<?= Url::to(['/owned-set/index']) ?>">
                    <span class="user-page-stat-icon user-page-stat-icon--owned"><i class="bi bi-box-seam-fill"></i></span>
                    <div class="user-page-stat-body">
                        <div class="user-page-stat-value"><?= (int)$ownedCount ?></div>
                        <div class="user-page-stat-label"><?= Html::encode(T::tr('Owned sets')) ?></div>
                    </div>
                </a>

                <a class="user-page-stat-tile user-page-stat-tile--compact" href="<?= Url::to(['/wishlist/index']) ?>">
                    <span class="user-page-stat-icon user-page-stat-icon--wishlist"><i class="bi bi-heart-fill"></i></span>
                    <div class="user-page-stat-body">
                        <div class="user-page-stat-value"><?= (int)$wishlistCount ?></div>
                        <div class="user-page-stat-label"><?= Html::encode(T::tr('On your wishlist')) ?></div>
                    </div>
                </a>

                <a class="user-page-stat-tile user-page-stat-tile--compact" href="<?= Url::to(['/user/reviews']) ?>">
                    <span class="user-page-stat-icon user-page-stat-icon--reviews"><i class="bi bi-journal-richtext"></i></span>
                    <div class="user-page-stat-body">
                        <div class="user-page-stat-value"><?= (int)$reviewCount ?></div>
                        <div class="user-page-stat-label"><?= Html::encode(T::tr('Your reviews')) ?></div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <?php
    $detailedReviewCount = (int)($reviewProfile['detailed_count'] ?? 0);
    $totalReviewCount = (int)($reviewProfile['total_count'] ?? 0);
    $userPreferences = is_array($reviewProfile['preferences'] ?? null) ? $reviewProfile['preferences'] : [];

    $hasReviewData = $detailedReviewCount > 0 || $totalReviewCount > 0;
    $preferenceQuestionKeys = ['set_purpose', 'priority'];
    $minReviewsForWidget = 3;
    $hasEnoughForWidgets = $detailedReviewCount >= $minReviewsForWidget;
    ?>

    <?php if ($hasReviewData): ?>
        <section class="user-page-card user-review-card mt-4">
            <div class="d-flex justify-content-between align-items-baseline mb-3 flex-wrap gap-2">
                <h3 class="user-page-card-title">
                    <i class="bi bi-stars text-warning"></i><?= Html::encode(T::tr('Your review profile')) ?>
                </h3>
                <span class="user-page-eyebrow">
                    <?= T::tr('{n, plural, =1{# review} other{# reviews}}', ['n' => $totalReviewCount]) ?>
                    <?php if ($detailedReviewCount > 0 && $detailedReviewCount !== $totalReviewCount): ?>
                        · <?= T::tr('{n} detailed', ['n' => $detailedReviewCount]) ?>
                    <?php endif; ?>
                </span>
            </div>

            <?php if (!$hasEnoughForWidgets): ?>
                <div class="user-review-empty">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <div class="fw-semibold mb-1"><?= Html::encode(T::tr('A few more detailed reviews unlock your taste profile')) ?></div>
                        <div class="small text-body-secondary">
                            <?= Html::encode(T::tr('Once you have at least {n} detailed reviews, we will show your preferences and how strictly you rate compared to others.', ['n' => $minReviewsForWidget])) ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php if ($userPreferences !== []): ?>
                        <div class="col-lg-6">
                            <div class="user-review-section">
                                <h6 class="user-review-section-title">
                                    <i class="bi bi-bullseye"></i><?= Html::encode(T::tr('What you look for in sets')) ?>
                                </h6>
                                <?php foreach ($preferenceQuestionKeys as $qKey): ?>
                                    <?php if (!isset($userPreferences[$qKey])) { continue; } ?>
                                    <?php
                                    $entry = $userPreferences[$qKey];
                                    $total = (int)$entry['total'];
                                    if ($total === 0) { continue; }
                                    ?>
                                    <div class="user-review-pref">
                                        <div class="user-review-pref-label">
                                            <?= Html::encode(SetReview::getQuestionLabel($qKey)) ?>
                                        </div>
                                        <div class="user-review-pref-bars">
                                            <?php foreach ($entry['counts'] as $value => $bucket): ?>
                                                <?php $pct = (int)$bucket['pct']; ?>
                                                <div class="user-review-pref-row">
                                                    <span class="user-review-pref-text">
                                                        <?= Html::encode(SetReview::getAnswerLabel($qKey, (string)$value)) ?>
                                                    </span>
                                                    <div class="user-review-pref-bar">
                                                        <div class="user-review-pref-bar-fill" style="--w: <?= (int)$pct ?>%"></div>
                                                    </div>
                                                    <span class="user-review-pref-pct"><?= (int)$pct ?>%</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasRadarData): ?>
                        <div class="col-lg-6">
                            <div class="user-review-section">
                                <h6 class="user-review-section-title">
                                    <i class="bi bi-radar"></i><?= Html::encode(T::tr('How you rate vs. the community')) ?>
                                </h6>
                                <div class="user-review-radar-wrap">
                                    <canvas data-role="radar-chart" data-chart="<?= Html::encode($radarChartJson) ?>"></canvas>
                                </div>
                                <?php if ($takeawayStrictest !== null || $takeawayGenerous !== null): ?>
                                    <div class="user-review-radar-takeaway small text-body-secondary mt-2">
                                        <?php if ($takeawayStrictest !== null): ?>
                                            <?= Html::encode(T::tr('You rate {dim} {n} points lower than the community average.', [
                                                'dim' => SetReview::getDimensionLabel($takeawayStrictest['dimension']),
                                                'n'   => number_format(abs($takeawayStrictest['delta']), 1, '.', ''),
                                            ])) ?>
                                        <?php endif; ?>
                                        <?php if ($takeawayGenerous !== null): ?>
                                            <?= Html::encode(T::tr('You rate {dim} {n} points higher than the community average.', [
                                                'dim' => SetReview::getDimensionLabel($takeawayGenerous['dimension']),
                                                'n'   => number_format(abs($takeawayGenerous['delta']), 1, '.', ''),
                                            ])) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($recommendedSets)): ?>
                    <div class="user-review-section mt-4">
                        <h6 class="user-review-section-title">
                            <i class="bi bi-people-fill"></i><?= Html::encode(T::tr('Reviewers like you also rated these highly')) ?>
                        </h6>
                        <div class="user-review-recs">
                            <?php foreach ($recommendedSets as $rec): ?>
                                <?php /** @var Set $set */ $set = $rec['set']; ?>
                                <a class="user-review-rec" href="<?= Url::to(['/lego/lego/view', 'slug' => $set->slug]) ?>">
                                    <div class="user-review-rec-img-wrap">
                                        <img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="<?= Html::encode((string)$set->name) ?>" loading="lazy">
                                    </div>
                                    <div class="user-review-rec-body">
                                        <div class="user-review-rec-number">#<?= Html::encode((string)$set->number) ?></div>
                                        <div class="user-review-rec-name"><?= Html::encode((string)$set->name) ?></div>
                                        <div class="user-review-rec-meta">
                                            <?php if ($set->rating !== null): ?>
                                                <span class="badge text-bg-warning text-dark">
                                                    <i class="bi bi-star-fill"></i> <?= Html::encode(number_format((float)$set->rating, 2, '.', '')) ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="small text-body-secondary">
                                                <i class="bi bi-people"></i>
                                                <?= T::tr('{n, plural, =1{# peer} other{# peers}}', ['n' => (int)$rec['peer_count']]) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>
