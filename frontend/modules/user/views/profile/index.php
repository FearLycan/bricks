<?php

use common\models\User;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var User $user
 * @var int  $ownedCount
 * @var int  $wishlistCount
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
    <h1 class="page-title mb-1">
        <i class="bi bi-person-vcard-fill text-primary me-2"></i>
        <?= Html::encode(T::tr('My profile')) ?>
    </h1>
    <p class="text-muted mb-4"><?= Html::encode(T::tr('A quick look at your account on BrickAtlas.')) ?></p>

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

        <div class="col-lg-5">
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

            <a class="user-page-stat-tile mb-3" href="<?= Url::to(['/owned-set/index']) ?>">
                <span class="user-page-stat-icon user-page-stat-icon--owned"><i class="bi bi-box-seam-fill"></i></span>
                <div class="user-page-stat-body">
                    <div class="user-page-stat-value"><?= (int)$ownedCount ?></div>
                    <div class="user-page-stat-label"><?= Html::encode(T::tr('Owned sets')) ?></div>
                </div>
                <i class="bi bi-arrow-right user-page-stat-arrow"></i>
            </a>

            <a class="user-page-stat-tile" href="<?= Url::to(['/wishlist/index']) ?>">
                <span class="user-page-stat-icon user-page-stat-icon--wishlist"><i class="bi bi-heart-fill"></i></span>
                <div class="user-page-stat-body">
                    <div class="user-page-stat-value"><?= (int)$wishlistCount ?></div>
                    <div class="user-page-stat-label"><?= Html::encode(T::tr('On your wishlist')) ?></div>
                </div>
                <i class="bi bi-arrow-right user-page-stat-arrow"></i>
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <?= Html::a(
                '<i class="bi bi-sliders me-2"></i>' . T::tr('Settings'), ['/user/settings'], ['class' => 'btn btn-primary', 'encode' => false]
        ) ?>
        <?= Html::a(
                '<i class="bi bi-box-arrow-right me-2"></i>' . T::tr('Logout'), ['/auth/logout'], ['class' => 'btn btn-outline-secondary', 'data-method' => 'post', 'encode' => false]
        ) ?>
    </div>
</div>
