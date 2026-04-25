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
    ];
    public $js       = [
        'libs/select2/select2.full.min.js',
        'libs/venobox/venobox.min.js',
        'js/common.js',
    ];
    public $depends  = [
        YiiAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        BootstrapIconAsset::class,
    ];
}
