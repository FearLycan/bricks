<?php

use frontend\components\MenuService;
use frontend\components\T;
use yii\helpers\Html;

$menu = new MenuService();
$items = $menu->getCategoryItems();
?>

<li class="nav-item dropdown">
    <a class="bricks-nav-link nav-link dropdown-toggle"
       href="#"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="false">
        <i class="bi bi-grid me-1"></i><?= Html::encode(T::tr('Categories')) ?>
    </a>
    <ul class="dropdown-menu">
        <?php foreach ($items as $item): ?>
            <li>
                <a class="dropdown-item d-flex align-items-center<?= !empty($item['modifier']) ? ' bricks-category-item--' . Html::encode((string)$item['modifier']) : '' ?>"
                   href="<?= Html::encode((string)$item['url']) ?>">
                    <i class="<?= Html::encode((string)$item['icon']) ?> me-2"></i>
                    <span><?= Html::encode((string)$item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</li>
