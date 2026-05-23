<?php

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View  $this
 * @var array $categories Output of LegoFaq::getCategories()
 */

$this->title = T::tr('Help and FAQ — BrickAtlas');
$this->params['metaDescription'] = T::tr('Answers to common questions about BrickAtlas — prices, wishlist, reviews, account and privacy.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/faq']);
$this->params['robots'] = 'index,follow';

$this->params['breadcrumbs'][] = T::tr('Help');
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/faq.css', ['depends' => [\frontend\assets\AppAsset::class]]);
?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Help'),
        'title'           => T::tr('How can we help?'),
        'intro'           => T::tr('Quick answers about prices, your wishlist, reviews and your account. Type a keyword to filter — or browse by topic.'),
        'icon'            => 'bi-life-preserver',
        'modifier'        => 'faq',
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <div class="faq-search-wrap">
        <i class="bi bi-search faq-search-icon"></i>
        <input type="search"
               class="faq-search-input"
               id="faqSearch"
               placeholder="<?= Html::encode(T::tr('Search the FAQ — try “wishlist”, “price” or “password”')) ?>"
               autocomplete="off"
               spellcheck="false"
               aria-label="<?= Html::encode(T::tr('Search FAQ')) ?>">
    </div>

    <div class="faq-layout">
        <aside class="faq-toc" aria-label="<?= Html::encode(T::tr('FAQ topics')) ?>">
            <p class="faq-toc-title"><?= Html::encode(T::tr('Topics')) ?></p>
            <ul class="faq-toc-list">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="faq-toc-link" href="#<?= Html::encode($category['slug']) ?>" data-faq-toc>
                            <i class="<?= Html::encode($category['icon']) ?> me-2"></i>
                            <span><?= Html::encode($category['name']) ?></span>
                            <span class="faq-toc-count"><?= count($category['items']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="faq-contact-card">
                <i class="bi bi-envelope-paper-heart"></i>
                <p class="faq-contact-text">
                    <?= Html::encode(T::tr('Didn’t find what you were looking for?')) ?>
                </p>
                <a href="<?= \yii\helpers\Url::to(['/contact']) ?>" class="faq-contact-cta">
                    <?= Html::encode(T::tr('Get in touch')) ?>
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </aside>

        <div class="faq-content" id="faqContent">
            <?php foreach ($categories as $category):
                $accordionId = 'faqAccordion-' . $category['slug'];
            ?>
                <section class="faq-category" id="<?= Html::encode($category['slug']) ?>" data-faq-category>
                    <header class="faq-category-header">
                        <h2 class="faq-category-title">
                            <i class="<?= Html::encode($category['icon']) ?> me-2"></i>
                            <?= Html::encode($category['name']) ?>
                        </h2>
                    </header>

                    <div class="accordion faq-accordion" id="<?= Html::encode($accordionId) ?>" data-faq-list>
                        <?php foreach ($category['items'] as $index => $row):
                            $itemId = $category['slug'] . '-' . $index;
                            $searchHaystack = mb_strtolower(
                                (string)$row['q'] . ' ' . (string)$row['a'],
                                'UTF-8'
                            );
                        ?>
                            <div class="accordion-item faq-item"
                                 data-faq-item
                                 data-search="<?= Html::encode($searchHaystack) ?>">
                                <h3 class="accordion-header" id="heading-<?= Html::encode($itemId) ?>">
                                    <button class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-<?= Html::encode($itemId) ?>"
                                            aria-expanded="false"
                                            aria-controls="collapse-<?= Html::encode($itemId) ?>">
                                        <?= Html::encode((string)$row['q']) ?>
                                    </button>
                                </h3>
                                <div id="collapse-<?= Html::encode($itemId) ?>"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="heading-<?= Html::encode($itemId) ?>">
                                    <div class="accordion-body">
                                        <?= nl2br(Html::encode((string)$row['a'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <p class="faq-empty" id="faqEmpty" hidden>
                <i class="bi bi-emoji-frown me-2"></i>
                <?= Html::encode(T::tr('Nothing matches that search. Try a different keyword.')) ?>
            </p>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    var input    = document.getElementById('faqSearch');
    var content  = document.getElementById('faqContent');
    var emptyMsg = document.getElementById('faqEmpty');
    if (!input || !content) {
        return;
    }

    var categories = content.querySelectorAll('[data-faq-category]');

    function normalize(value) {
        return (value || '').toString().toLowerCase().trim();
    }

    function applyFilter() {
        var query = normalize(input.value);
        var anyVisible = false;

        categories.forEach(function (category) {
            var items = category.querySelectorAll('[data-faq-item]');
            var visibleInCat = 0;
            items.forEach(function (item) {
                var matches = query === '' || (item.getAttribute('data-search') || '').indexOf(query) !== -1;
                item.hidden = !matches;
                if (matches) {
                    visibleInCat += 1;
                }
            });
            category.hidden = visibleInCat === 0;
            if (visibleInCat > 0) {
                anyVisible = true;
            }
        });

        emptyMsg.hidden = anyVisible || query === '';
        if (query === '') {
            emptyMsg.hidden = true;
        } else {
            emptyMsg.hidden = anyVisible;
        }
    }

    input.addEventListener('input', applyFilter);

    // TOC smooth-scroll with sticky-header offset.
    document.querySelectorAll('[data-faq-toc]').forEach(function (link) {
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

    // Deep-link support: ?q=password opens the FAQ pre-filtered.
    var params = new URLSearchParams(window.location.search);
    var q = params.get('q');
    if (q) {
        input.value = q;
        applyFilter();
    }
})();
JS);
?>
