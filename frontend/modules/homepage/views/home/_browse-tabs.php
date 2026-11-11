<?php

use yii\helpers\Html;
use yii\web\View;

/**
 * @var View  $this
 * @var array $tabs ['key' => ['label' => string, 'tiles' => [['label','url','image','cover','accent'], ...]], ...]
 */

$nonEmpty = array_filter($tabs, static fn(array $tab) => !empty($tab['tiles']));
if (empty($nonEmpty)) {
    return;
}

$tabsId = uniqid('bricks-browse-', false);
$firstKey = array_key_first($nonEmpty);
?>

    <section class="bricks-section bricks-section--browse-tabs">
        <div class="container">
            <ul class="bricks-browse-tabs" role="tablist" id="<?= Html::encode($tabsId) ?>">
                <?php foreach ($nonEmpty as $key => $tab): ?>
                    <li role="presentation">
                        <button type="button"
                                class="bricks-browse-tab <?= $key === $firstKey ? 'is-active' : '' ?>"
                                data-tab-target="<?= Html::encode($tabsId . '-' . $key) ?>"
                                role="tab"
                                aria-selected="<?= $key === $firstKey ? 'true' : 'false' ?>">
                            <?= Html::encode($tab['label']) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php foreach ($nonEmpty as $key => $tab): ?>
                <div class="bricks-browse-tab-panel <?= $key === $firstKey ? 'is-active' : '' ?>"
                     id="<?= Html::encode($tabsId . '-' . $key) ?>"
                     role="tabpanel">
                    <div class="bricks-browse-grid">
                        <?php foreach ($tab['tiles'] as $tile):
                            $image  = $tile['image'] ?? null;                    // hand-made artwork
                            $cover  = $image ? null : ($tile['cover'] ?? null);   // otherwise a set photo
                            $accent = !empty($tile['accent']) ? ' bricks-ctile--' . $tile['accent'] : '';
                        ?>
                            <a href="<?= Html::encode($tile['url']) ?>" class="bricks-ctile bricks-ctile--browse<?= Html::encode($accent) ?><?= $image ? ' has-art' : '' ?>">
                                <span class="bricks-ctile-stage">
                                    <?php if ($image): ?>
                                        <img class="bricks-ctile-art" src="<?= Html::encode($image) ?>" alt="" loading="lazy" decoding="async">
                                    <?php elseif ($cover): ?>
                                        <img class="bricks-ctile-photo" src="<?= Html::encode($cover) ?>" alt="" loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <i class="bi <?= Html::encode($tile['icon'] ?? 'bi-grid-3x3-gap') ?> bricks-ctile-icon" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </span>
                                <span class="bricks-ctile-foot">
                                    <span class="bricks-ctile-title"><?= Html::encode($tile['label']) ?></span>
                                    <i class="bi bi-arrow-right bricks-ctile-go" aria-hidden="true"></i>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php
$js = <<<JS
(function () {
    var root = document.getElementById('{$tabsId}');
    if (!root || root.dataset.bricksBrowseInit === '1') {
        return;
    }
    root.dataset.bricksBrowseInit = '1';
    var buttons = root.querySelectorAll('.bricks-browse-tab');
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-tab-target');
            buttons.forEach(function (b) {
                var active = b === btn;
                b.classList.toggle('is-active', active);
                b.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            var section = root.parentElement;
            section.querySelectorAll('.bricks-browse-tab-panel').forEach(function (p) {
                p.classList.toggle('is-active', p.id === target);
            });
        });
    });
})();
JS;
$this->registerJs($js, View::POS_END);
