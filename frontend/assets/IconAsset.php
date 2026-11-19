<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Self-hosted Phosphor icons (phosphoricons.com, MIT), subset to the icons
 * the site actually uses. Regenerate with tools/icons/build.py after adding
 * an icon anywhere in the code.
 *
 * Markup: <i class="ph ph-heart"></i> (regular), ph-fill (filled),
 * ph-duotone (two-tone).
 */
class IconAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';
    public $css      = [
        'css/icons.css',
    ];
}
