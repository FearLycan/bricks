<?php

namespace frontend\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Select2 (filter dropdowns on catalog listings). Registered only where it is
 * used (modules/lego/views/lego/_search.php) so other pages do not pay for its
 * render-blocking CSS and JS.
 */
class Select2Asset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';
    public $css      = [
        'libs/select2/select2.min.css',
        'libs/select2/select2-bootstrap-5-theme.min.css',
    ];
    public $js       = [
        'libs/select2/select2.full.min.js',
    ];
    public $depends  = [
        JqueryAsset::class,
        BootstrapAsset::class,
    ];
}
