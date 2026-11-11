<?php

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View  $this
 * @var array $tiles Output of LegoInterests::getTiles()
 */

$this->title = T::tr('LEGO® Sets by Interest - Star Wars, Marvel and More');
$this->params['metaDescription'] = T::tr('LEGO sets grouped by interest: Adults Welcome, Star Wars, Magazines, Polybags, Exclusive, Harry Potter and more.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/interests']);
$this->params['robots'] = 'index,follow';

$this->params['breadcrumbs'][] = T::tr('Interests');
$this->params['fullWidth'] = true;

$this->registerCssFile('@web/css/interests.css', ['depends' => [\frontend\assets\AppAsset::class]]);

// Group tiles by their `group` attribute, preserving original order.
$groups = [];
foreach ($tiles as $tile) {
    $groupName = (string)($tile['group'] ?? T::tr('Browse'));
    if (!isset($groups[$groupName])) {
        $groups[$groupName] = [];
    }
    $groups[$groupName][] = $tile;
}

$resolveImage = static function (?string $image): ?string {
    $image = $image !== null ? trim($image) : '';
    if ($image === '') {
        return null;
    }

    return preg_match('#^(https?:)?//#', $image) ? $image : '/' . ltrim($image, '/');
};
?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Browse'),
        'title'           => T::tr('Shop by interest'),
        'intro'           => T::tr('Shortcuts into the catalog. Pick what you\'re into and we\'ll show matching sets.'),
        'icon'            => 'bi-compass',
        'modifier'        => 'interests',
        'showBreadcrumbs' => true,
]) ?>

<div class="container">
    <?php foreach ($groups as $groupName => $groupTiles): ?>
        <section class="interests-group">
            <h2 class="interests-group-title"><?= Html::encode($groupName) ?></h2>
            <div class="interests-grid">
                <?php foreach ($groupTiles as $tile):
                    $art = $tile['art'] ?? null;
                    $photo = $resolveImage($tile['image'] ?? null);
                    $cardClass = 'interests-card interests-card--' . (string)($tile['modifier'] ?? 'default');
                    if ($art !== null) {
                        $cardClass .= ' has-art';
                    }
                ?>
                    <a class="<?= Html::encode($cardClass) ?>" href="<?= Html::encode(Url::to($tile['url'])) ?>">
                        <span class="interests-card-stage">
                            <?php if ($art !== null): ?>
                                <img class="interests-card-art" src="<?= Html::encode($art) ?>" alt="" loading="lazy" decoding="async">
                            <?php elseif ($photo !== null): ?>
                                <img class="interests-card-photo" src="<?= Html::encode($photo) ?>" alt="" loading="lazy" decoding="async">
                            <?php else: ?>
                                <i class="interests-card-icon <?= Html::encode($tile['icon']) ?>" aria-hidden="true"></i>
                            <?php endif; ?>
                        </span>
                        <span class="interests-card-foot">
                            <span class="interests-card-title"><?= Html::encode($tile['name']) ?></span>
                            <span class="interests-card-desc"><?= Html::encode($tile['description']) ?></span>
                            <span class="interests-card-go">
                                <?= Html::encode(T::tr('Browse sets')) ?>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
