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

$this->title = T::tr('LEGO® Sets by Interest — Star Wars, Marvel and More');
$this->params['metaDescription'] = T::tr('Discover LEGO sets curated by interest — Adults Welcome, Star Wars, Magazines, Polybags, Exclusive, Harry Potter, and more.');
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
    if (preg_match('#^(https?:)?//#', $image)) {
        return $image;
    }
    $relative = ltrim($image, '/');
    if (is_file(Yii::getAlias('@webroot') . '/' . $relative)) {
        return Yii::getAlias('@web') . '/' . $relative;
    }
    // Theme images stored in DB are typically `/uploads/...` paths — accept them as-is.
    return '/' . $relative;
};
?>

<?= $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Browse'),
        'title'           => T::tr('Shop by interest'),
        'intro'           => T::tr('Shortcuts into the catalog — pick what you’re into and we’ll show you matching sets.'),
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
                    $imageUrl = $resolveImage($tile['image'] ?? null);
                    $cardClass = 'interests-tile';
                    if (!empty($tile['modifier'])) {
                        $cardClass .= ' interests-tile--' . Html::encode((string)$tile['modifier']);
                    }
                    if ($imageUrl !== null) {
                        $cardClass .= ' interests-tile--has-image';
                    }
                ?>
                    <a class="<?= $cardClass ?>"
                       href="<?= Html::encode(Url::to($tile['url'])) ?>"
                       <?php if ($imageUrl !== null): ?>
                           style="background-image: url('<?= Html::encode($imageUrl) ?>');"
                       <?php endif; ?>>
                        <span class="interests-tile-overlay"></span>
                        <span class="interests-tile-body">
                            <span class="interests-tile-icon"><i class="<?= Html::encode($tile['icon']) ?>"></i></span>
                            <span class="interests-tile-name"><?= Html::encode($tile['name']) ?></span>
                            <span class="interests-tile-description"><?= Html::encode($tile['description']) ?></span>
                            <span class="interests-tile-cta">
                                <?= Html::encode(T::tr('Browse sets')) ?>
                                <i class="bi bi-arrow-right ms-1"></i>
                            </span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
