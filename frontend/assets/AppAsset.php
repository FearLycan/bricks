<?php

namespace frontend\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapIconAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';
    // select2 lives in Select2Asset (catalog filters only); venobox CSS in
    // DeferredStylesAsset (non-blocking).
    public $css      = [
        'css/site.css',
        'css/page-hero.css',
    ];
    public $js       = [
        'libs/venobox/venobox.min.js',
        // Chart.js is loaded on demand by js/review.js (radar charts only).
        'js/common.js',
        'js/search.js',
        'js/wizard.js',
        'js/offer-tracking.js',
        'js/effects.js',
        'js/wishlist.js',
        'js/owned-set.js',
        'js/review.js',
    ];
    public $depends  = [
        YiiAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        BootstrapIconAsset::class,
        DeferredStylesAsset::class,
    ];
}
