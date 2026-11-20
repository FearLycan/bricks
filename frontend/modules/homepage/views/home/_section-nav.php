<?php

use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Shortcut tiles under the hero + a compact bar that sticks under the header
 * once the tiles scroll away and highlights the section in view.
 * Targets are section ids on the homepage; an item whose section is not on
 * the page (e.g. empty "Coming soon") is removed by the script. An item
 * with a 4th element links to that URL instead (no section, not removed).
 *
 * @var View $this
 */

$items = [
    ['new-arrivals', 'ph-duotone ph-sparkle', T::tr('New arrivals')],
    ['by-age', 'ph-duotone ph-baby', T::tr('By age')],
    ['themes', 'ph-duotone ph-puzzle-piece', T::tr('Themes')],
    ['gift-ideas', 'ph-duotone ph-gift', T::tr('Gift ideas')],
    ['coming-soon', 'ph-duotone ph-hourglass-medium', T::tr('Coming soon')],
    ['for-adults', 'ph-duotone ph-armchair', T::tr('For adults')],
];

// Gift season (15 Nov - 24 Dec): gifts first, the advent calendars page
// instead of "For adults" (which the gift picker already covers).
if (\frontend\components\GiftGuide::isGiftSeason()) {
    $items = [
        ['new-arrivals', 'ph-duotone ph-sparkle', T::tr('New arrivals')],
        ['gift-ideas', 'ph-duotone ph-gift', T::tr('Gift ideas')],
        ['advent', 'ph-duotone ph-calendar-dots', T::tr('Advent calendars'), Url::to(['/lego/lego/advent-calendars'])],
        ['themes', 'ph-duotone ph-puzzle-piece', T::tr('Themes')],
        ['by-age', 'ph-duotone ph-baby', T::tr('By age')],
        ['coming-soon', 'ph-duotone ph-hourglass-medium', T::tr('Coming soon')],
    ];
}
?>
<nav class="bricks-snav" aria-label="<?= Html::encode(T::tr('Homepage sections')) ?>">
    <div class="container">
        <div class="bricks-snav-tiles">
            <?php foreach ($items as $item): ?>
                <?php [$id, $icon, $label] = $item; $url = $item[3] ?? null; ?>
                <a class="bricks-snav-tile" href="<?= $url !== null ? Html::encode($url) : '#' . $id ?>"<?= $url === null ? ' data-snav="' . $id . '"' : '' ?>>
                    <i class="<?= $icon ?>" aria-hidden="true"></i>
                    <span><?= Html::encode($label) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
<div class="bricks-snav-bar" aria-hidden="true" hidden>
    <div class="container">
        <div class="bricks-snav-bar-inner">
            <?php foreach ($items as $item): ?>
                <?php [$id, $icon, $label] = $item; $url = $item[3] ?? null; ?>
                <a class="bricks-snav-pill" href="<?= $url !== null ? Html::encode($url) : '#' . $id ?>"<?= $url === null ? ' data-snav="' . $id . '"' : '' ?> tabindex="-1">
                    <i class="<?= $icon ?>" aria-hidden="true"></i><?= Html::encode($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
(function () {
    var tiles = document.querySelector('.bricks-snav');
    var bar = document.querySelector('.bricks-snav-bar');
    if (!tiles || !bar) { return; }

    // Drop shortcuts whose section is not rendered.
    document.querySelectorAll('[data-snav]').forEach(function (a) {
        if (!document.getElementById(a.getAttribute('data-snav'))) { a.remove(); }
    });

    var header = document.querySelector('.bricks-header, header.fixed-top, .navbar.fixed-top');
    function headerHeight() { return header ? header.getBoundingClientRect().height : 0; }
    function syncOffset() {
        var h = headerHeight();
        bar.style.top = h + 'px';
        document.documentElement.style.setProperty('--snav-offset', (h + 64) + 'px');
    }
    syncOffset();
    window.addEventListener('resize', syncOffset);

    if (!('IntersectionObserver' in window)) { return; }

    // Show the bar once the tiles are out of view.
    new IntersectionObserver(function (entries) {
        var out = !entries[0].isIntersecting && entries[0].boundingClientRect.top < 0;
        bar.hidden = !out;
        bar.setAttribute('aria-hidden', out ? 'false' : 'true');
        bar.querySelectorAll('a').forEach(function (a) { a.tabIndex = out ? 0 : -1; });
    }, { rootMargin: '-' + Math.round(headerHeight()) + 'px 0px 0px 0px' }).observe(tiles);

    // Highlight the section in view.
    var pills = {};
    bar.querySelectorAll('a').forEach(function (a) { pills[a.getAttribute('data-snav')] = a; });
    var visible = {};
    var spy = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { visible[e.target.id] = e.isIntersecting; });
        var current = null;
        Object.keys(pills).forEach(function (id) { if (!current && visible[id]) { current = id; } });
        Object.keys(pills).forEach(function (id) {
            pills[id].classList.toggle('is-active', id === current);
            if (id === current && bar.scrollWidth > bar.clientWidth) {
                pills[id].scrollIntoView({ block: 'nearest', inline: 'center' });
            }
        });
    }, { rootMargin: '-35% 0px -55% 0px' });
    Object.keys(pills).forEach(function (id) { spy.observe(document.getElementById(id)); });
})();
JS);
