<?php

use yii\helpers\Html;
use yii\web\View;

/**
 * @var View  $this
 * @var array $tabs ['key' => ['label' => string, 'tiles' => [['label','url','image','accent'], ...]], ...]
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
                        <?php foreach ($tab['tiles'] as $tile): ?>
                            <a href="<?= Html::encode($tile['url']) ?>"
                               class="bricks-browse-tile <?= !empty($tile['accent']) ? 'bricks-browse-tile--' . Html::encode($tile['accent']) : '' ?>">
                                <span class="bricks-browse-tile-media" style="background-image: url('<?= Html::encode($tile['image']) ?>');"></span>
                                <span class="bricks-browse-tile-overlay"></span>
                                <span class="bricks-browse-tile-label"><?= Html::encode($tile['label']) ?></span>
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
