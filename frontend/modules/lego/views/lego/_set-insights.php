<?php

use common\models\Set;
use frontend\components\SetInsights;
use frontend\components\T;
use yii\helpers\Html;

/**
 * "Compared with the rest of the theme" box (SetInsights::forSet()).
 *
 * @var $model Set
 * @var $insights array
 */

$theme = $insights['theme'];
$name = (string)$model->name;
$f = Yii::$app->formatter;
$month = static fn(?string $date): string => $date === null ? '' : SetInsights::formatMonth($date);

$size = $insights['size'];
$ppp = $insights['ppp'];
$dates = $insights['dates'];
$minifigs = $insights['minifigs'];

// Cards
$cards = [];
if ($ppp !== null) {
    $cards[] = [
        'label'  => T::tr('Price per piece'),
        'value'  => Set::formatAmountFromCents($ppp['cents'], 'USD'),
        'bar'    => [$ppp['position'], T::tr('cheaper'), T::tr('pricier')],
        'note'   => T::tr('median in the theme: {value}', ['value' => Set::formatAmountFromCents($ppp['medianCents'], 'USD')]),
    ];
}
if ($size !== null) {
    $cards[] = [
        'label' => T::tr('Size'),
        'value' => T::tr('{n, plural, one{# piece} other{# pieces}}', ['n' => $size['pieces']]),
        'bar'   => [$size['position'], $f->asInteger($size['min']), $f->asInteger($size['max'])],
        'neutral' => true,
        'note'  => T::tr('median in the theme: {value}', ['value' => $f->asInteger((int)round($size['median']))]),
    ];
}
if ($dates['upcoming']) {
    $cards[] = ['label' => T::tr('Release'), 'value' => $month($dates['launch']), 'bar' => null, 'note' => ''];
} elseif ($dates['ended'] && $dates['exit'] !== null) {
    $cards[] = [
        'label' => T::tr('On sale until'),
        'value' => $month($dates['exit']),
        'bar'   => null,
        'note'  => $dates['launch'] !== null ? T::tr('release: {date}', ['date' => $month($dates['launch'])]) : '',
    ];
} elseif ($dates['launch'] !== null) {
    $cards[] = [
        'label' => T::tr('In stores since'),
        'value' => $month($dates['launch']),
        'bar'   => null,
        'note'  => $dates['exit'] !== null ? T::tr('expected to retire: {date}', ['date' => $month($dates['exit'])]) : '',
    ];
}

// Sentences: what the cards do not say on their own.
$sentences = [];
if ($size !== null) {
    if ($size['percentile'] <= 10) {
        $sentences[] = T::tr('{name} is among the 10% smallest sets in {theme}.', ['name' => $name, 'theme' => $theme]);
    } elseif ($size['percentile'] >= 90) {
        $sentences[] = T::tr('{name} is among the 10% largest sets in {theme}.', ['name' => $name, 'theme' => $theme]);
    } else {
        $sentences[] = T::tr('{name} has more pieces than {percent}% of {theme} sets.', ['name' => $name, 'theme' => $theme, 'percent' => $size['percentile']]);
    }
}
if ($ppp !== null) {
    $years = ['theme' => $theme, 'from' => $ppp['fromYear'], 'to' => $ppp['toYear']];
    if ($ppp['cheaperThan'] >= 60) {
        $sentences[] = T::tr('Per piece it costs less than {percent}% of {theme} sets from {from}–{to}.', $years + ['percent' => $ppp['cheaperThan']]);
    } elseif ($ppp['pricierThan'] >= 60) {
        $sentences[] = T::tr('Per piece it costs more than {percent}% of {theme} sets from {from}–{to}.', $years + ['percent' => $ppp['pricierThan']]);
    } else {
        $sentences[] = T::tr('Its price per piece is close to the {theme} median for {from}–{to}.', $years);
    }
}
if ($dates['typicalMonths'] !== null) {
    if ($dates['ended'] && $dates['months'] !== null) {
        $sentences[] = T::tr('It was on sale for {months, plural, one{# month} other{# months}}; {theme} sets usually stay on sale for about {typical, plural, one{# month} other{# months}}.', [
            'months' => $dates['months'], 'typical' => $dates['typicalMonths'], 'theme' => $theme,
        ]);
    } else {
        $sentences[] = T::tr('{theme} sets usually stay on sale for about {months, plural, one{# month} other{# months}}.', ['theme' => $theme, 'months' => $dates['typicalMonths']]);
    }
}
if ($minifigs !== null) {
    $sentences[] = T::tr('Minifigures: {count} (typical for {theme}: {typical}).', ['count' => $minifigs['count'], 'typical' => $minifigs['typical'], 'theme' => $theme]);
}
?>
<section class="lego-insights" aria-labelledby="lego-insights-title">
    <h2 class="lego-insights-title" id="lego-insights-title"><?= Html::encode(T::tr('Compared with the rest of {theme}', ['theme' => $theme])) ?></h2>
    <?php if ($cards !== []): ?>
        <div class="lego-insights-cards lego-insights-cards--<?= count($cards) ?>">
            <?php foreach ($cards as $card): ?>
                <div class="lego-insights-card">
                    <div class="lego-insights-label"><?= Html::encode($card['label']) ?></div>
                    <div class="lego-insights-value"><?= Html::encode($card['value']) ?></div>
                    <?php if ($card['bar'] !== null): ?>
                        <div class="lego-insights-bar<?= !empty($card['neutral']) ? ' lego-insights-bar--neutral' : '' ?>" aria-hidden="true"><span style="left: <?= (int)$card['bar'][0] ?>%"></span></div>
                        <div class="lego-insights-bar-labels" aria-hidden="true"><span><?= Html::encode($card['bar'][1]) ?></span><span><?= Html::encode($card['bar'][2]) ?></span></div>
                    <?php endif; ?>
                    <?php if ($card['note'] !== ''): ?>
                        <div class="lego-insights-note"><?= Html::encode($card['note']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($sentences !== []): ?>
        <p class="lego-insights-text"><?= Html::encode(SetInsights::joinSentences($sentences)) ?></p>
    <?php endif; ?>
    <div class="lego-insights-source"><?= Html::encode(T::tr('Calculated by BrickAtlas from LEGO list prices (USD) and Brickset dates.')) ?></div>
</section>
