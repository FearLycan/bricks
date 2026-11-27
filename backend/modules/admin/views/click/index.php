<?php

use common\models\OfferClick;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var int $days
 * @var int $bots
 * @var int[] $ranges
 * @var array{clicks: int|string, offer: int|string|null, lego: int|string|null, sets: int|string} $totals
 * @var int $previous
 * @var int $botCount
 * @var array<string, int|string> $daily
 * @var int $from
 * @var list<array{name: string, n: int|string}> $stores
 * @var list<array{set_id: int, number: ?string, name: ?string, slug: ?string, n: int|string, offer: int|string, lego: int|string}> $sets
 * @var list<array{lang: string, n: int|string}> $langs
 * @var list<array{device: string, n: int|string}> $devices
 * @var OfferClick[] $recent
 */

$this->title = 'Store clicks';
$this->params['breadcrumbs'][] = $this->title;

$clicks = (int)$totals['clicks'];
$setUrl = static fn(?string $slug): ?string => $slug ? Yii::$app->frontendUrlManager->createAbsoluteUrl(["/lego/{$slug}"]) : null;
$rangeLabel = static fn(int $d): string => $d === 1 ? 'Today' : ($d === 365 ? '1 year' : $d . ' days');
$delta = $previous > 0 ? (int)round(($clicks - $previous) / $previous * 100) : null;

// One bar per day, also for days without clicks.
$series = [];
for ($t = $from; $t <= time(); $t += 86400) {
    $day = date('Y-m-d', $t);
    $series[$day] = (int)($daily[$day] ?? 0);
}
$maxDay = max(1, ...array_values($series ?: [0]));

$bar = static function (int $n, int $max): string {
    $pct = $max > 0 ? max(2, (int)round($n / $max * 100)) : 0;

    return '<div class="progress mt-1" style="height:4px;max-width:220px"><div class="progress-bar bg-dark" style="width:' . $pct . '%"></div></div>';
};
?>
<div class="click-index">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary small">
                "View offer" and "Check official LEGO price" on set pages, counted by our own script (no cookies, admins skipped).
                <?= $bots ? 'Showing <b>suspected bots</b>.' : 'Humans only.' ?>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="btn-group btn-group-sm" role="group" aria-label="Period">
                <?php foreach ($ranges as $r): ?>
                    <?= Html::a($rangeLabel($r), ['index', 'days' => $r, 'bots' => $bots], ['class' => 'btn ' . ($r === $days ? 'btn-dark' : 'btn-outline-secondary')]) ?>
                <?php endforeach; ?>
            </div>
            <?= Html::a($bots ? 'Show humans' : 'Show bots (' . $botCount . ')', ['index', 'days' => $days, 'bots' => $bots ? 0 : 1], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="text-body-secondary small text-uppercase fw-semibold">Clicks</div>
            <div class="fs-3 fw-bold"><?= $clicks ?>
                <?php if ($delta !== null): ?><span class="badge <?= $delta >= 0 ? 'text-bg-success' : 'text-bg-danger' ?> fs-6 align-middle"><?= ($delta >= 0 ? '+' : '') . $delta ?>%</span><?php endif; ?>
            </div>
            <div class="text-body-secondary small"><?= $previous ?> in the previous <?= $days === 1 ? 'day' : $days . ' days' ?></div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="text-body-secondary small text-uppercase fw-semibold">Store offers</div>
            <div class="fs-3 fw-bold"><?= (int)$totals['offer'] ?></div>
            <div class="text-body-secondary small">Affiliate and marketplace links</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="text-body-secondary small text-uppercase fw-semibold">LEGO.com</div>
            <div class="fs-3 fw-bold"><?= (int)$totals['lego'] ?></div>
            <div class="text-body-secondary small">Official price button (not affiliate)</div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="text-body-secondary small text-uppercase fw-semibold">Sets</div>
            <div class="fs-3 fw-bold"><?= (int)$totals['sets'] ?></div>
            <div class="text-body-secondary small">Different sets clicked</div>
        </div></div></div>
    </div>

    <?php if (count($series) > 1): ?>
        <div class="card mb-3"><div class="card-body">
            <h2 class="h6">Day by day</h2>
            <div class="d-flex align-items-end gap-1" style="height:120px">
                <?php foreach ($series as $day => $n): ?>
                    <div class="flex-fill bg-dark rounded-top" title="<?= Html::encode($day . ': ' . $n) ?>"
                         style="min-width:2px;height:<?= $n > 0 ? max(3, (int)round($n / $maxDay * 100)) : 1 ?>%;opacity:<?= $n > 0 ? '.8' : '.12' ?>"></div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between text-body-secondary small mt-1">
                <span><?= Html::encode(array_key_first($series)) ?></span><span>max <?= $maxDay ?>/day</span><span><?= Html::encode(array_key_last($series)) ?></span>
            </div>
        </div></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-lg-7"><div class="card h-100"><div class="card-body">
            <h2 class="h6">Top sets</h2>
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Set</th><th class="text-end">Offers</th><th class="text-end">LEGO</th><th class="text-end">All</th></tr></thead>
                <tbody>
                <?php $max = (int)($sets[0]['n'] ?? 0); foreach ($sets as $s): $url = $setUrl($s['slug']); ?>
                    <tr>
                        <td>
                            <?= $url ? Html::a(Html::encode(trim(($s['number'] ?? '') . ' ' . ($s['name'] ?? ''))), $url, ['target' => '_blank', 'rel' => 'noopener']) : '#' . (int)$s['set_id'] ?>
                            <?= $bar((int)$s['n'], $max) ?>
                        </td>
                        <td class="text-end"><?= (int)$s['offer'] ?></td>
                        <td class="text-end"><?= (int)$s['lego'] ?></td>
                        <td class="text-end fw-semibold"><?= (int)$s['n'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($sets === []): ?><tr><td colspan="4" class="text-body-secondary">No clicks in this period.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div></div></div>
        <div class="col-lg-5">
            <div class="card mb-3"><div class="card-body">
                <h2 class="h6">Stores</h2>
                <table class="table table-sm mb-0"><tbody>
                <?php $max = (int)($stores[0]['n'] ?? 0); foreach ($stores as $s): ?>
                    <tr><td><?= Html::encode($s['name']) ?><?= $bar((int)$s['n'], $max) ?></td><td class="text-end"><?= (int)$s['n'] ?></td></tr>
                <?php endforeach; ?>
                <?php if ($stores === []): ?><tr><td class="text-body-secondary">None.</td></tr><?php endif; ?>
                </tbody></table>
            </div></div>
            <div class="card"><div class="card-body">
                <h2 class="h6">Language and device</h2>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <?php foreach ($langs as $l): ?><span class="badge text-bg-light border"><?= Html::encode($l['lang']) ?> · <?= (int)$l['n'] ?></span><?php endforeach; ?>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($devices as $d): ?><span class="badge text-bg-light border"><?= Html::encode($d['device']) ?> · <?= (int)$d['n'] ?></span><?php endforeach; ?>
                </div>
            </div></div>
        </div>
    </div>

    <div class="card"><div class="card-body">
        <h2 class="h6">Latest <?= $bots ? 'bot ' : '' ?>clicks</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Time</th><th>Set</th><th>Store</th><th class="text-end">Price</th><th>Lang</th><th>Device</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $c): $url = $setUrl($c->set?->slug); ?>
                    <tr>
                        <td class="text-nowrap small"><?= Html::encode(date('Y-m-d H:i', $c->created_at)) ?></td>
                        <td><?= $url ? Html::a(Html::encode(trim($c->set->number . ' ' . $c->set->name)), $url, ['target' => '_blank', 'rel' => 'noopener']) : '#' . $c->set_id ?></td>
                        <td><?= Html::encode($c->kind === OfferClick::KIND_LEGO ? 'LEGO.com' : ($c->store?->name ?? '(deleted store)')) ?></td>
                        <td class="text-end text-nowrap"><?= $c->price !== null ? Html::encode(number_format($c->price / 100, 2) . ' ' . $c->currency_code) : '–' ?></td>
                        <td><?= Html::encode((string)$c->lang) ?></td>
                        <td><?= Html::encode((string)$c->device) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recent === []): ?><tr><td colspan="6" class="text-body-secondary">Nothing yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div></div>
</div>
