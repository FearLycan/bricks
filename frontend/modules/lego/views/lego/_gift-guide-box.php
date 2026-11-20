<?php

use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * "Recommended in our gift guide" box on the set page.
 *
 * @var $box array{guide: array<string, mixed>, text: string, covers: \common\models\Set[], also: array<int, array<string, mixed>>}
 */

$guide = $box['guide'];
$url = Url::to(['/lego/lego/gift-guide', 'slug' => $guide['slug']]);
?>
<aside class="lego-guide-box"
       style="--lg-accent: <?= Html::encode($guide['accent']) ?>; --lg-soft: <?= Html::encode($guide['soft']) ?>;"
       aria-label="<?= Html::encode(T::tr('Recommended in our gift guide')) ?>">
    <?php if ($box['covers'] !== []): ?>
        <a class="lego-guide-box-collage" href="<?= Html::encode($url) ?>" tabindex="-1" aria-hidden="true">
            <?php foreach ($box['covers'] as $set): ?>
                <span><img src="<?= Html::encode($set->getDisplayMainImageUrl()) ?>" alt="" loading="lazy"></span>
            <?php endforeach; ?>
        </a>
    <?php endif; ?>
    <div class="lego-guide-box-body">
        <div class="lego-guide-box-kicker">
            <i class="ph ph-gift" aria-hidden="true"></i>
            <?= Html::encode(T::tr('Recommended in our gift guide')) ?>
        </div>
        <a class="lego-guide-box-title" href="<?= Html::encode($url) ?>"><?= Html::encode($guide['title']) ?></a>
        <p class="lego-guide-box-text"><?= Html::encode($box['text']) ?></p>
        <?php if ($box['also'] !== []): ?>
            <div class="lego-guide-box-also">
                <?= Html::encode(T::tr('Also in:')) ?>
                <?php foreach ($box['also'] as $i => $other): ?>
                    <?= $i > 0 ? ', ' : '' ?><a href="<?= Html::encode(Url::to(['/lego/lego/gift-guide', 'slug' => $other['slug']])) ?>" style="--lg-also: <?= Html::encode($other['accent']) ?>"><?= Html::encode($other['title']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <a class="lego-guide-box-cta" href="<?= Html::encode($url) ?>">
        <?= Html::encode(T::tr('See the guide')) ?>
        <i class="ph ph-arrow-right" aria-hidden="true"></i>
    </a>
</aside>
