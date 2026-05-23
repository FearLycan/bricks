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
    public $css      = [
        'libs/select2/select2.min.css',
        'libs/select2/select2-bootstrap-5-theme.min.css',
        'libs/venobox/venobox.min.css',
        'css/site.css',
        'css/page-hero.css',
    ];
    public $js       = [
        'libs/select2/select2.full.min.js',
        'libs/venobox/venobox.min.js',
        ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', 'crossorigin' => 'anonymous'],
        'js/common.js',
        'js/search.js',
        'js/wizard.js',
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
    ];
}
