<?php

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View  $this
 * @var array $categories Output of LegoGlossary::getCategories()
 */

$this->title = T::tr('LEGO Glossary: AFOL acronyms and slang explained');
$this->params['metaDescription'] = T::tr('Plain-language definitions of LEGO acronyms and slang. AFOL, MOC, SNOT, UCS, MISB and the rest explained.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/glossary']);
$this->params['robots'] = 'index,follow';

$this->params['breadcrumbs'][] = T::tr('Glossary');
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/glossary.css', ['depends' => [\frontend\assets\AppAsset::class]]);
?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('For AFOLs'),
        'title'           => T::tr('LEGO Glossary'),
        'intro'           => T::tr('What every acronym and bit of slang on this site actually means.'),
        'icon'            => 'bi-journal-text',
        'modifier'        => 'glossary',
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <div class="glossary-toolbar">
        <div class="glossary-search-wrap">
            <i class="bi bi-search glossary-search-icon"></i>
            <input type="search"
                   class="glossary-search-input"
                   id="glossarySearch"
                   placeholder="<?= Html::encode(T::tr('Search — try “AFOL” or “sealed box”')) ?>"
                   autocomplete="off"
                   spellcheck="false"
                   aria-label="<?= Html::encode(T::tr('Search glossary')) ?>">
        </div>
        <div class="glossary-sort-wrap">
            <span class="glossary-sort-label"><?= Html::encode(T::tr('Sort')) ?>:</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="<?= Html::encode(T::tr('Sort glossary')) ?>">
                <button type="button" class="btn btn-outline-secondary active" data-glossary-sort="default">
                    <?= Html::encode(T::tr('Default')) ?>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-glossary-sort="alpha">
                    <i class="bi bi-sort-alpha-down me-1"></i><?= Html::encode(T::tr('A → Z')) ?>
                </button>
            </div>
        </div>
    </div>

    <div class="glossary-layout">
        <aside class="glossary-toc" aria-label="<?= Html::encode(T::tr('Glossary categories')) ?>">
            <p class="glossary-toc-title"><?= Html::encode(T::tr('Categories')) ?></p>
            <ul class="glossary-toc-list">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="glossary-toc-link" href="#<?= Html::encode($category['slug']) ?>" data-glossary-toc>
                            <i class="<?= Html::encode($category['icon']) ?> me-2"></i>
                            <span><?= Html::encode($category['name']) ?></span>
                            <span class="glossary-toc-count"><?= count($category['terms']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="glossary-content" id="glossaryContent">
            <?php foreach ($categories as $category): ?>
                <section class="glossary-category" id="<?= Html::encode($category['slug']) ?>" data-glossary-category>
                    <header class="glossary-category-header">
                        <h2 class="glossary-category-title">
                            <i class="<?= Html::encode($category['icon']) ?> me-2"></i>
                            <?= Html::encode($category['name']) ?>
                        </h2>
                        <?php if (!empty($category['intro'])): ?>
                            <p class="glossary-category-intro"><?= Html::encode($category['intro']) ?></p>
                        <?php endif; ?>
                    </header>

                    <dl class="glossary-terms" data-glossary-list>
                        <?php foreach ($category['terms'] as $row): ?>
                            <?php
                                $searchHaystack = mb_strtolower(
                                    (string)$row['term'] . ' '
                                    . (string)($row['full'] ?? '') . ' '
                                    . (string)$row['definition'],
                                    'UTF-8'
                                );
                            ?>
                            <div class="glossary-term"
                                 data-glossary-term
                                 data-search="<?= Html::encode($searchHaystack) ?>"
                                 data-sort-key="<?= Html::encode(mb_strtolower((string)$row['term'], 'UTF-8')) ?>">
                                <dt class="glossary-term-head">
                                    <span class="glossary-term-name"><?= Html::encode((string)$row['term']) ?></span>
                                    <?php if (!empty($row['full'])): ?>
                                        <span class="glossary-term-full"><?= Html::encode((string)$row['full']) ?></span>
                                    <?php endif; ?>
                                </dt>
                                <dd class="glossary-term-body">
                                    <?= Html::encode((string)$row['definition']) ?>
                                </dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </section>
            <?php endforeach; ?>

            <p class="glossary-empty" id="glossaryEmpty" hidden>
                <i class="bi bi-emoji-frown me-2"></i>
                <?= Html::encode(T::tr('Nothing matches that search. Try a different keyword.')) ?>
            </p>
        </div>
    </div>
</div>

<?php
$jsTexts = [
    'noResults' => T::tr('Nothing matches that search. Try a different keyword.'),
];
$jsConfig = json_encode($jsTexts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$this->registerJs(<<<JS
(function () {
    var input    = document.getElementById('glossarySearch');
    var content  = document.getElementById('glossaryContent');
    var emptyMsg = document.getElementById('glossaryEmpty');
    if (!input || !content) {
        return;
    }

    var categories = content.querySelectorAll('[data-glossary-category]');
    var sortButtons = document.querySelectorAll('[data-glossary-sort]');

    function normalize(value) {
        return (value || '').toString().toLowerCase().trim();
    }

    function applyFilter() {
        var query = normalize(input.value);
        var anyVisible = false;

        categories.forEach(function (category) {
            var terms = category.querySelectorAll('[data-glossary-term]');
            var visibleInCat = 0;
            terms.forEach(function (term) {
                var matches = query === '' || (term.getAttribute('data-search') || '').indexOf(query) !== -1;
                term.hidden = !matches;
                if (matches) {
                    visibleInCat += 1;
                }
            });
            category.hidden = visibleInCat === 0;
            if (visibleInCat > 0) {
                anyVisible = true;
            }
        });

        emptyMsg.hidden = anyVisible;
    }

    function applySort(mode) {
        sortButtons.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-glossary-sort') === mode);
        });

        categories.forEach(function (category) {
            var list = category.querySelector('[data-glossary-list]');
            if (!list) { return; }
            var rows = Array.prototype.slice.call(list.querySelectorAll('[data-glossary-term]'));
            if (mode === 'alpha') {
                rows.sort(function (a, b) {
                    return a.getAttribute('data-sort-key').localeCompare(b.getAttribute('data-sort-key'));
                });
            } else {
                rows.sort(function (a, b) {
                    return parseInt(a.dataset.originalOrder, 10) - parseInt(b.dataset.originalOrder, 10);
                });
            }
            rows.forEach(function (row) {
                list.appendChild(row);
            });
        });
    }

    // Remember original DOM order so "Default" can restore it after A→Z sort.
    categories.forEach(function (category) {
        var rows = category.querySelectorAll('[data-glossary-term]');
        rows.forEach(function (row, index) {
            row.dataset.originalOrder = index;
        });
    });

    input.addEventListener('input', applyFilter);
    sortButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            applySort(btn.getAttribute('data-glossary-sort'));
        });
    });

    // Smooth scroll for TOC anchors so the sticky header doesn't cover the target.
    document.querySelectorAll('[data-glossary-toc]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var hash = link.getAttribute('href');
            var target = hash ? document.querySelector(hash) : null;
            if (!target) { return; }
            event.preventDefault();
            var menuHeight = (document.getElementById('menu-navbar') || {}).offsetHeight || 0;
            var top = target.getBoundingClientRect().top + window.pageYOffset - menuHeight - 12;
            window.scrollTo({ top: top, behavior: 'smooth' });
            if (history.replaceState) { history.replaceState(null, '', hash); }
        });
    });
})();
JS);
?>
