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
 * @var View                $this
 * @var array<string,mixed> $guide  See frontend\components\GiftGuide.
 * @var array<string,Set>   $sets   Sets still on sale, keyed by number.
 */

$route = ['/lego/lego/gift-guide', 'slug' => $guide['slug']];

$this->title = $guide['metaTitle'];
$this->params['metaDescription'] = $guide['description'];
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl($route);
$this->params['robots'] = 'index,follow';
$this->params['socialTitle'] = $guide['title'];
$this->params['socialDescription'] = $guide['description'];
$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];
$this->params['breadcrumbs'][] = $guide['title'];
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/gift-guide.css', ['depends' => [\frontend\assets\AppAsset::class]]);

// FAQPage structured data from the same questions shown on the page.
$faqSchema = [
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(static fn(array $qa) => [
        '@type'          => 'Question',
        'name'           => $qa[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
    ], $guide['faq']),
];
?>

<?= JsonLdRenderer::render([$faqSchema]) ?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Gift guide'),
        'title'           => $guide['title'],
        'intro'           => T::tr($guide['intro'], ['n' => count($sets)]),
        'image'           => null,
        'icon'            => $guide['icon'],
        'modifier'        => $guide['modifier'],
        'showBreadcrumbs' => true,
]) ?>

<div class="container gift-guide">
    <section class="gift-guide-block">
        <h2 class="gift-guide-h2"><?= Html::encode($guide['tipsTitle']) ?></h2>
        <ul class="gift-guide-tips">
            <?php foreach ($guide['tips'] as [$lead, $text]): ?>
                <li><strong><?= Html::encode($lead) ?></strong> <?= Html::encode($text) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php foreach ($guide['sections'] as $section): ?>
        <?php
        $sectionSets = [];
        foreach ($section['sets'] as $number => $why) {
            if (isset($sets[(string)$number])) {
                $sectionSets[] = [$sets[(string)$number], $why];
            }
        }
        if ($sectionSets === []) {
            continue;
        }
        ?>
        <section class="gift-guide-block">
            <h2 class="gift-guide-h2">
                <i class="bi <?= Html::encode($section['icon']) ?>" aria-hidden="true"></i>
                <?= Html::encode($section['title']) ?>
            </h2>
            <p class="gift-guide-lead"><?= Html::encode($section['lead']) ?></p>
            <div class="row g-3">
                <?php foreach ($sectionSets as [$set, $why]): ?>
                    <?php
                    /** @var Set $set */
                    $url = Url::to(['/lego/lego/view', 'modules' => 'lego', 'slug' => $set->slug]);
                    $image = $set->getMainImage()->url ?? null;
                    $theme = $set->theme->name ?? '';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="gift-guide-card">
                            <a class="gift-guide-card-img" href="<?= Html::encode($url) ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($image !== null): ?>
                                    <img src="<?= Html::encode($image) ?>" alt="" loading="lazy" decoding="async">
                                <?php endif; ?>
                                <?php if ($set->isRetiringSoon()): ?>
                                    <span class="gift-guide-badge"><?= Html::encode(T::tr('Retiring soon')) ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="gift-guide-card-body">
                                <div class="gift-guide-card-meta"><?= Html::encode(trim($set->number . ($theme !== '' ? ' · ' . $theme : ''))) ?></div>
                                <h3 class="gift-guide-card-title">
                                    <a href="<?= Html::encode($url) ?>"><?= Html::encode($set->name) ?></a>
                                </h3>
                                <div class="gift-guide-card-facts">
                                    <?php if ($set->age !== null): ?>
                                        <span><i class="bi bi-cake" aria-hidden="true"></i> <?= Html::encode($set->age) ?>+</span>
                                    <?php endif; ?>
                                    <?php if ($set->pieces !== null): ?>
                                        <span><i class="bi bi-columns-gap" aria-hidden="true"></i> <?= Html::encode(T::tr('{n} pieces', ['n' => Yii::$app->formatter->asInteger($set->pieces)])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="gift-guide-card-why"><?= Html::encode($why) ?></p>
                                <a class="btn btn-sm btn-outline-primary gift-guide-card-cta" href="<?= Html::encode($url) ?>">
                                    <?= Html::encode(T::tr('See the set and offers')) ?>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <section class="gift-guide-wizard">
        <div>
            <h2 class="gift-guide-h2 mb-1"><?= Html::encode(T::tr('Nothing here quite fits?')) ?></h2>
            <p class="mb-0"><?= Html::encode(T::tr('Answer a few questions about budget and interests and the set finder will suggest more sets.')) ?></p>
        </div>
        <button type="button" class="btn btn-danger"
                data-wizard-answers="<?= Html::encode(Json::encode($guide['wizard'])) ?>"
                data-wizard-step="3">
            <i class="bi bi-magic me-1" aria-hidden="true"></i><?= Html::encode(T::tr('Open the set finder')) ?>
        </button>
    </section>

    <section class="gift-guide-block">
        <h2 class="gift-guide-h2"><?= Html::encode(T::tr('Frequently asked questions')) ?></h2>
        <?php foreach ($guide['faq'] as [$question, $answer]): ?>
            <div class="gift-guide-faq">
                <h3><?= Html::encode($question) ?></h3>
                <p><?= Html::encode($answer) ?></p>
            </div>
        <?php endforeach; ?>
        <p class="gift-guide-note"><?= Html::encode(T::tr('Click a set to see the current offers and prices.')) ?></p>
    </section>
</div>
