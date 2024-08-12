<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;
use yii\bootstrap5\BootstrapAsset;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';
    public $css      = [
        'bootscore/css/main.css',
        'bootscore/fontawesome/css/all.min.css',
        'bootscore/lib/OwlCarousel2/assets/owl.carousel.min.css',
        'bootscore/lib/OwlCarousel2/assets/owl.theme.default.min.css',
        'bootscore/lib/baguetteBox/baguetteBox.min.css',
        'css/site.css',
    ];
    public $js       = [
        'bootscore/js/lib/bootstrap.bundle.min.js',
        'bootscore/lib/OwlCarousel2/owl.carousel.min.js',
        'bootscore/lib/baguetteBox/baguetteBox.min.js',
        'bootscore/js/theme.js',
    ];
    public $depends  = [
        YiiAsset::class,
        //BootstrapAsset::class,
    ];
}
