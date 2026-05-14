<?php

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$currentLanguage = (string)Yii::$app->language;

$names = [
    'en' => 'English',
    'pl' => 'Polski',
    'de' => 'Deutsch',
    'fr' => 'Français',
    'es' => 'Español',
    'it' => 'Italiano',
    'ja' => '日本語',
    'zh' => '中文',
];

$flagUrl = static function (string $language): string {
    return Url::to('@web/images/flags/' . $language . '.svg');
};

$renderFlag = static function (string $language) use ($flagUrl, $names): string {
    return Html::img($flagUrl($language), [
        'class' => 'bricks-flag',
        'alt'   => '',
        'width' => 24,
        'height' => 16,
        'loading' => 'lazy',
        'decoding' => 'async',
    ]);
};

$currentName = $names[$currentLanguage] ?? $names['en'];
?>

<div class="dropdown bricks-language-switcher">
    <button class="btn btn-link bricks-nav-link nav-link dropdown-toggle d-flex align-items-center gap-2 px-2"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="<?= Html::encode(T::tr('Change language')) ?>: <?= Html::encode($currentName) ?>">
        <?= $renderFlag($currentLanguage) ?>
        <span class="visually-hidden"><?= Html::encode($currentName) ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach (SeoHelper::SUPPORTED_LANGUAGES as $language): ?>
            <?php
            $name = $names[$language] ?? $language;
            $isActive = $language === $currentLanguage;
            $href = SeoHelper::buildCurrentUrlInLanguage($language);
            ?>
            <li>
                <?= Html::a(
                    $renderFlag($language) . '<span>' . Html::encode($name) . '</span>',
                    $href,
                    [
                        'class'    => 'dropdown-item d-flex align-items-center gap-2' . ($isActive ? ' active' : ''),
                        'encode'   => false,
                        'rel'      => $isActive ? null : 'alternate',
                        'hreflang' => $language,
                    ]
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
