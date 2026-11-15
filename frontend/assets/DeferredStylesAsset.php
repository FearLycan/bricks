<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Stylesheets not needed for the first paint (e.g. the VenoBox lightbox, used
 * only after a click). Loaded without blocking rendering: media="print" until
 * loaded, then switched to "all".
 */
class DeferredStylesAsset extends AssetBundle
{
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';
    public $css        = [
        'libs/venobox/venobox.min.css',
    ];
    public $cssOptions = [
        'media'  => 'print',
        'onload' => "this.media='all'",
    ];
}
