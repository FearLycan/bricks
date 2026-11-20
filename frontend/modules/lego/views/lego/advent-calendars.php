<?php

use common\models\Set;
use common\schema\JsonLdRenderer;
use frontend\components\Helper;
use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * /lego/advent-calendars: this season's calendars in a comparison table,
 * then a card per calendar with who it suits, tips, last year's calendars
 * still on sale and an FAQ. Data: frontend\components\AdventCalendars.
 *
 * @var View                                                                       $this
 * @var int                                                                        $year
 * @var array{current: list<array<string, mixed>>, older: list<array<string, mixed>>} $calendars
 * @var int|null                                                                   $daysLeft
 */

$current = $calendars['current'];
$older = $calendars['older'];
$route = ['/lego/lego/advent-calendars'];
$fmt = static fn(?array $p): string => $p !== null ? Set::formatAmountFromCents($p['cents'], $p['currency']) : '–';
$setUrl = static fn(Set $s): string => Url::to("/lego/{$s->slug}");

$prices = array_filter(array_column($current, 'price'));
$minPrice = $prices !== [] ? $fmt(array_reduce($prices, static fn($c, $p) => $c === null || $p['cents'] < $c['cents'] ? $p : $c)) : null;
$maxPrice = $prices !== [] ? $fmt(array_reduce($prices, static fn($c, $p) => $c === null || $p['cents'] > $c['cents'] ? $p : $c)) : null;
$seriesList = implode(', ', array_column($current, 'series'));

$title = T::tr('LEGO advent calendars {year}', ['year' => $year]);
$description = T::tr('All LEGO advent calendars for {year} compared: {series}. Price, pieces, age and who each one suits, plus tips on buying before 1 December.', ['year' => $year, 'series' => $seriesList]);

$this->title = T::tr('LEGO advent calendars {year}: compare {series} - BrickAtlas', ['year' => $year, 'series' => $seriesList]);
$this->params['metaDescription'] = $description;
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl($route);
$this->params['robots'] = 'index,follow';
$this->params['socialTitle'] = $title;
$this->params['socialDescription'] = $description;
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = T::tr('Advent calendars');
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/gift-guide.css', ['depends' => [\frontend\assets\AppAsset::class]]);

$faq = [
    [T::tr('When should I buy a LEGO advent calendar?'), T::tr('Before the end of November. The first door opens on 1 December, and popular calendars often sell out in the last week of November.')],
    [T::tr('How much does a LEGO advent calendar cost?'), $minPrice !== null
        ? T::tr('This year\'s calendars cost from {min} to {max} at the LEGO list price. Shops are often cheaper; open a calendar to compare current offers.', ['min' => $minPrice, 'max' => $maxPrice])
        : T::tr('Open a calendar to see its list price and compare current offers.')],
    [T::tr('Which LEGO advent calendar is best for a 5-year-old?'), T::tr('One rated 5+, such as City or Disney. Every door is a small build of a few minutes that a five-year-old can manage with a little help.')],
];

$schema = [
    [
        '@type'           => 'ItemList',
        'name'            => $title,
        'itemListElement' => array_map(static fn(array $row, int $i) => [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => (string)$row['set']->name,
            'url'      => SeoHelper::buildAbsoluteUrl("/lego/{$row['set']->slug}"),
        ], $current, array_keys($current)),
    ],
    [
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(static fn(array $qa) => [
            '@type'          => 'Question',
            'name'           => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
        ], $faq),
    ],
];

$aside = '';
if ($daysLeft !== null) {
    $aside = '<div class="advent-countdown"><b>' . (int)$daysLeft . '</b><span>'
        . Html::encode(T::tr('{n, plural, =1{day} other{days}} to 1 December', ['n' => $daysLeft])) . '</span></div>';
}
?>

<?= JsonLdRenderer::render($schema) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Seasonal'),
        'title'           => $title,
        'intro'           => T::tr('All of this year\'s calendars in one place: what is inside, who each one suits and how many pieces it has. Open a calendar to compare shop prices.'),
        'image'           => null,
        'icon'            => null,
        'modifier'        => 'christmas',
        'showBreadcrumbs' => true,
        'aside'           => $aside,
]) ?>

<div class="container gift-guide advent">
    <?php if ($current === []): ?>
        <p class="gift-guide-lead"><?= Html::encode(T::tr('This year\'s advent calendars have not been announced yet.')) ?></p>
    <?php else: ?>
        <section class="gift-guide-block">
            <h2 class="gift-guide-h2"><?= Html::encode(T::tr('{year} calendars compared', ['year' => $year])) ?></h2>
            <p class="gift-guide-lead"><?= Html::encode(T::tr('{n, plural, =1{# calendar} other{# calendars}}, each with 24 doors.', ['n' => count($current)])) ?></p>
            <div class="table-responsive">
                <table class="advent-table">
                    <thead>
                    <tr>
                        <th><?= Html::encode(T::tr('Calendar')) ?></th>
                        <th><?= Html::encode(T::tr('List price')) ?></th>
                        <th><?= Html::encode(T::tr('Pieces')) ?></th>
                        <th><?= Html::encode(T::tr('Age')) ?></th>
                        <th><?= Html::encode(T::tr('Price per piece')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($current as $row): ?>
                        <?php /** @var Set $set */ $set = $row['set']; ?>
                        <tr>
                            <td>
                                <a class="advent-name" href="<?= Html::encode($setUrl($set)) ?>">
                                    <span class="advent-thumb"><img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="" loading="lazy"></span>
                                    <span><b><?= Html::encode($row['series']) ?></b> <small><?= Html::encode((string)$set->number) ?></small></span>
                                </a>
                            </td>
                            <td><b><?= Html::encode($fmt($row['price'])) ?></b></td>
                            <td><?= (int)$set->pieces ?></td>
                            <td><?= $set->age !== null ? (int)$set->age . '+' : '–' ?></td>
                            <td>
                                <?= $row['ppp'] !== null ? Html::encode(Yii::$app->formatter->asDecimal($row['ppp'], 3) . ' ' . $row['price']['currency']) : '–' ?>
                                <?php if ($row['best']): ?>
                                    <span class="advent-best"><?= Html::encode(T::tr('best')) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="gift-guide-block">
            <h2 class="gift-guide-h2"><?= Html::encode(T::tr('Which calendar for whom')) ?></h2>
            <div class="advent-cards">
                <?php foreach ($current as $row): ?>
                    <?php $set = $row['set']; ?>
                    <article class="advent-card">
                        <a class="advent-card-img" href="<?= Html::encode($setUrl($set)) ?>" tabindex="-1" aria-hidden="true">
                            <img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="" loading="lazy">
                        </a>
                        <div class="advent-card-body">
                            <div class="advent-card-meta"><?= Html::encode($set->number . ' · ' . $row['series']) ?></div>
                            <h3><?= Html::encode($row['who']) ?></h3>
                            <p><?= Html::encode($row['text']) ?></p>
                            <div class="advent-card-facts">
                                <span><b><?= (int)$set->pieces ?></b> <?= Html::encode(T::tr('pcs')) ?></span>
                                <?php if ($set->age !== null): ?><span><b><?= (int)$set->age ?>+</b></span><?php endif; ?>
                                <span><b><?= Html::encode($fmt($row['price'])) ?></b></span>
                            </div>
                            <a class="btn btn-outline-primary btn-sm" href="<?= Html::encode($setUrl($set)) ?>"><?= Html::encode(T::tr('See set and offers')) ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="gift-guide-block">
        <h2 class="gift-guide-h2"><?= Html::encode(T::tr('Before you buy')) ?></h2>
        <ul class="gift-guide-tips">
            <li><strong><?= Html::encode(T::tr('Buy before 1 December.')) ?></strong> <?= Html::encode(T::tr('The first door opens on 1 December, and popular calendars can disappear from shops in the last week of November.')) ?></li>
            <li><strong><?= Html::encode(T::tr('The age is a minimum.')) ?></strong> <?= Html::encode(T::tr('Each door is a few minutes of building. Calendars rated 5+ and 6+ can be built by a younger child alone or with a little help.')) ?></li>
            <li><strong><?= Html::encode(T::tr('Not only for kids.')) ?></strong> <?= Html::encode(T::tr('Many adult fans buy the Star Wars and Marvel calendars: after Christmas the 24 micro models end up on a shelf.')) ?></li>
        </ul>
    </section>

    <?php if ($older !== []): ?>
        <section class="gift-guide-block">
            <h2 class="gift-guide-h2"><?= Html::encode(T::tr('Last year\'s calendars that may still be in shops')) ?></h2>
            <p class="gift-guide-lead"><?= Html::encode(T::tr('These series have no calendar this year. Last year\'s version is sometimes still available, often for less.')) ?></p>
            <div class="advent-older">
                <?php foreach ($older as $row): ?>
                    <?php $set = $row['set']; ?>
                    <a href="<?= Html::encode($setUrl($set)) ?>">
                        <span class="advent-thumb"><img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="" loading="lazy"></span>
                        <span><b><?= Html::encode($row['series'] . ' ' . $set->year) ?></b><br>
                            <small><?= Html::encode(($set->age !== null ? (int)$set->age . '+ · ' : '') . (int)$set->pieces . ' ' . T::tr('pcs')) ?></small></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="gift-guide-block">
        <h2 class="gift-guide-h2"><?= Html::encode(T::tr('Frequently asked questions')) ?></h2>
        <?php foreach ($faq as [$question, $answer]): ?>
            <div class="gift-guide-faq">
                <h3><?= Html::encode($question) ?></h3>
                <p><?= Html::encode($answer) ?></p>
            </div>
        <?php endforeach; ?>
        <p class="gift-guide-note">
            <a href="<?= Html::encode(Url::to(['/lego/lego/gifts'])) ?>"><?= Html::encode(T::tr('More gift ideas')) ?></a>
        </p>
    </section>
</div>
