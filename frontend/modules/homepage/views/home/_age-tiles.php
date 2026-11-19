<?php

use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * "LEGO by age" row: set photo + coloured footer with the age range.
 *
 * @var array<int, array{range: string, label: string, url: string, cover: ?string, color: string, color2: string, soft: string}> $tiles
 */

if (empty($tiles)) {
    return;
}
?>
<section class="bricks-section bricks-section--age" id="by-age">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3 flex-wrap">
            <h2 class="bricks-section-title mb-0"><?= T::tr('LEGO{sup} by age', ['sup' => '<sup>®</sup>']) ?></h2>
            <a class="bricks-section-see-all" href="<?= Url::to(['/lego']) ?>"><?= Html::encode(T::tr('Browse all sets')) ?></a>
        </div>
        <div class="bricks-age-grid">
            <?php foreach ($tiles as $tile): ?>
                <a class="bricks-age-tile" href="<?= Html::encode($tile['url']) ?>"
                   style="--age-c: <?= Html::encode($tile['color']) ?>; --age-c2: <?= Html::encode($tile['color2']) ?>; --age-soft: <?= Html::encode($tile['soft']) ?>">
                    <span class="bricks-age-photo">
                        <?php if (!empty($tile['cover'])): ?>
                            <img src="<?= Html::encode($tile['cover']) ?>" alt="" loading="lazy" decoding="async">
                        <?php endif; ?>
                    </span>
                    <span class="bricks-age-foot">
                        <b><?= Html::encode($tile['range']) ?></b>
                        <span><?= Html::encode($tile['label']) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
