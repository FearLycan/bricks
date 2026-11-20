<?php

use common\models\Set;
use common\schema\JsonLdRenderer;
use frontend\components\Helper;
use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/**
 * /lego/gifts: hub listing every gift guide as a card with 3 of its sets,
 * plus links to the age listings, the set finder and retiring sets.
 *
 * @var View                                                        $this
 * @var list<array{guide: array<string, mixed>, covers: Set[]}>     $cards
 */

$route = ['/lego/lego/gifts'];
$title = T::tr('LEGO gift ideas');
$description = T::tr('Hand-picked LEGO gift guides for kids aged 5 to 8 and 9 to 12, teenagers and adults: sets grouped by interest, with tips on choosing the right age and size.');

$this->title = T::tr('LEGO gift ideas for kids, teens and adults - BrickAtlas');
$this->params['metaDescription'] = $description;
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl($route);
$this->params['robots'] = 'index,follow';
$this->params['socialTitle'] = $title;
$this->params['socialDescription'] = $description;
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('Gift ideas');
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/gift-guide.css', ['depends' => [\frontend\assets\AppAsset::class]]);

$itemList = [
    '@type'           => 'ItemList',
    'name'            => $title,
    'itemListElement' => array_map(static fn(array $card, int $i) => [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $card['guide']['title'],
        'url'      => SeoHelper::buildAbsoluteUrl(['/lego/lego/gift-guide', 'slug' => $card['guide']['slug']]),
    ], $cards, array_keys($cards)),
];

$ageLinks = [
    'for-toddlers' => T::tr('For toddlers (1–4)'),
    'for-kids'     => T::tr('For kids (5–8)'),
    'for-tweens'   => T::tr('Ages 9–12'),
    'for-teens'    => T::tr('For teens'),
    'for-adults'   => T::tr('For adults'),
    'small-builds' => T::tr('Small sets'),
];
?>

<?= JsonLdRenderer::render([$itemList]) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Gift guides'),
        'title'           => T::tr('LEGO as a gift'),
        'intro'           => T::tr('Sets picked by hand for kids, teenagers and adults. Each guide groups them by interest and helps you choose the right age and size.'),
        'image'           => null,
        'icon'            => null,
        'modifier'        => 'gifts',
        'showBreadcrumbs' => true,
]) ?>

<div class="container gift-hub">
    <div class="gift-hub-grid">
        <?php foreach ($cards as $card): ?>
            <?php $guide = $card['guide']; ?>
            <a class="gift-hub-card"
               href="<?= Html::encode(Url::to(['/lego/lego/gift-guide', 'slug' => $guide['slug']])) ?>"
               style="--gh-accent: <?= Html::encode($guide['accent']) ?>; --gh-soft: <?= Html::encode($guide['soft']) ?>;">
                <span class="gift-hub-collage">
                    <?php foreach ($card['covers'] as $set): ?>
                        <span><img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="<?= Html::encode((string)$set->name) ?>" loading="lazy"></span>
                    <?php endforeach; ?>
                </span>
                <span class="gift-hub-body">
                    <span class="gift-hub-age"><?= Html::encode($guide['ageLabel']) ?></span>
                    <span class="gift-hub-title"><?= Html::encode($guide['title']) ?></span>
                    <span class="gift-hub-teaser"><?= Html::encode($guide['teaser']) ?></span>
                    <span class="gift-hub-cta">
                        <?= Html::encode(T::tr('See the guide')) ?>
                        <i class="ph ph-arrow-right" aria-hidden="true"></i>
                    </span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="gift-hub-more">
        <section class="gift-hub-box">
            <h2><?= Html::encode(T::tr('All sets by age')) ?></h2>
            <div class="gift-hub-chips">
                <?php foreach ($ageLinks as $slug => $label): ?>
                    <a class="gift-hub-chip" href="<?= Html::encode(Url::to(['/lego/lego/audience', 'slug' => $slug])) ?>"><?= Html::encode($label) ?></a>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="gift-hub-box">
            <h2><?= Html::encode(T::tr('Not sure what to choose?')) ?></h2>
            <p><?= Html::encode(T::tr('Answer a few questions about who it is for, age, budget and interests, and the set finder will suggest sets.')) ?></p>
            <button type="button" class="gift-hub-btn"
                    data-wizard-answers="<?= Html::encode(Json::encode(['recipient' => 'gift'])) ?>"
                    data-wizard-step="2">
                <?= Html::encode(T::tr('Open the gift finder')) ?>
            </button>
            <p class="gift-hub-retiring">
                <a href="<?= Html::encode(Url::to(['/lego/lego/advent-calendars'])) ?>"><?= Html::encode(T::tr('LEGO advent calendars {year}', ['year' => \frontend\components\AdventCalendars::seasonYear()])) ?></a>
                <?= Html::encode(T::tr('all compared in one table.')) ?>
            </p>
            <p class="gift-hub-retiring">
                <a href="<?= Html::encode(Url::to(['/lego/lego/retiring-soon'])) ?>"><?= Html::encode(T::tr('Sets retiring soon')) ?></a>
                <?= Html::encode(T::tr('are worth buying before the holidays.')) ?>
            </p>
        </section>
    </div>
</div>
