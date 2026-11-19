<?php

use common\models\Set;
use common\models\Theme;
use frontend\components\T;
use frontend\components\ThemePalette;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Theme of the day: the theme's colour panel with name, CTA and the newest
 * sets as text links, next to up to 4 picked sets on a light tint of the
 * theme colour. Data: HomepageContentService::getThemeSpotlightSets().
 *
 * @var View                                                                $this
 * @var Theme|null                                                          $theme
 * @var array{picks: list<array{set: Set, badge: ?string}>, news: Set[]}    $sets
 */

if (!$theme instanceof Theme) {
    return;
}

$picks     = $sets['picks'] ?? [];
$news      = $sets['news'] ?? [];
$setsCount = $theme->sets_count !== null ? (int)$theme->sets_count : null;
$themeUrl  = Url::to("/lego/theme/{$theme->slug}");
[$accent, $soft] = ThemePalette::forSlug((string)$theme->slug);

$badgeLabel = static function (string $badge, Set $set): string {
    return match ($badge) {
        'biggest'    => T::tr('Biggest set'),
        'top-rated'  => T::tr('Top rated · {rating}', ['rating' => Yii::$app->formatter->asDecimal((float)$set->rating, 1)]),
        'best-ppp'   => T::tr('Best price per piece'),
        'retiring'   => T::tr('Retiring soon'),
        'collectors' => T::tr('For collectors'),
        default      => '',
    };
};
?>

<section class="bricks-spotlight<?= $picks === [] ? ' bricks-spotlight--solo' : '' ?>"
         style="--sp-accent: <?= Html::encode($accent) ?>; --sp-soft: <?= Html::encode($soft) ?>;">
    <div class="container">
        <div class="bricks-spotlight-box">
            <div class="bricks-spotlight-text">
                <span class="bricks-spotlight-eyebrow"><?= Html::encode(T::tr('Theme spotlight')) ?></span>
                <h2 class="bricks-spotlight-title"><?= Html::encode((string)$theme->name) ?></h2>
                <div class="bricks-spotlight-meta">
                    <?php if ($setsCount !== null): ?>
                        <span><?= T::tr('{n} sets', ['n' => $setsCount]) ?></span>
                    <?php endif; ?>
                    <?php if ($theme->year_from): ?>
                        <span><?= $theme->year_to
                            ? Html::encode($theme->year_from . '–' . $theme->year_to)
                            : Html::encode(T::tr('since {year}', ['year' => (string)$theme->year_from])) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= $themeUrl ?>" class="bricks-spotlight-cta"><?= Html::encode(T::tr('Explore the theme')) ?></a>

                <?php if ($news !== []): ?>
                    <div class="bricks-spotlight-news">
                        <div class="bricks-spotlight-news-label"><?= Html::encode(T::tr('Newest in this theme')) ?></div>
                        <div class="bricks-spotlight-chips">
                            <?php foreach ($news as $set): ?>
                                <a class="bricks-spotlight-chip" href="<?= Url::to("/lego/{$set->slug}") ?>">
                                    <b><?= Html::encode(T::tr('New release')) ?></b><?= Html::encode((string)$set->name) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($picks !== []): ?>
                <div class="bricks-spotlight-sets">
                    <?php foreach ($picks as $pick): ?>
                        <?php
                        /** @var Set $set */
                        $set   = $pick['set'];
                        $badge = $pick['badge'] !== null ? $badgeLabel($pick['badge'], $set) : '';
                        $price = $set->getLocalRetailPrice();
                        ?>
                        <a class="bricks-spotlight-card" href="<?= Url::to("/lego/{$set->slug}") ?>">
                            <span class="bricks-spotlight-card-img">
                                <?php if ($badge !== ''): ?>
                                    <span class="bricks-spotlight-badge<?= $pick['badge'] === 'retiring' ? ' is-warn' : '' ?>"><?= Html::encode($badge) ?></span>
                                <?php endif; ?>
                                <img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="<?= Html::encode((string)$set->name) ?>" loading="lazy">
                            </span>
                            <span class="bricks-spotlight-card-body">
                                <span class="bricks-spotlight-card-name"><?= Html::encode((string)$set->name) ?></span>
                                <span class="bricks-spotlight-card-meta">
                                    <?= Html::encode((string)$set->number) ?>
                                    · <?= T::tr('{n} pcs', ['n' => (int)$set->pieces]) ?>
                                    <?php if ($set->age !== null): ?>· <?= (int)$set->age ?>+<?php endif; ?>
                                </span>
                                <?php if ($price !== null): ?>
                                    <span class="bricks-spotlight-card-price">
                                        <b><?= Html::encode(Set::formatAmountFromCents($price['cents'], $price['currency'])) ?></b>
                                        <small><?= number_format($price['cents'] / 100 / (int)$set->pieces, 3) ?> <?= Html::encode($price['currency']) ?>/pcs</small>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
