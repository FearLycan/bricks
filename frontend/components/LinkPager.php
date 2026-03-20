<?php

namespace frontend\components;

use yii\bootstrap5\LinkPager as BootstrapLinkPager;

class LinkPager extends BootstrapLinkPager
{
    public $maxButtonCount = 7;
    //public $firstPageLabel = '<i class="bi bi-chevron-double-left"></i>';
    //public $lastPageLabel = '<i class="bi bi-chevron-double-right"></i>';
    public $prevPageLabel        = '<i class="bi bi-chevron-left"></i>';
    public $nextPageLabel        = '<i class="bi bi-chevron-right"></i>';
    public $hideOnSinglePage     = true;
    public $linkOptions          = ['class' => 'page-link'];
    public $activePageCssClass   = 'active';
    public $disabledPageCssClass = 'disabled';
    public $options              = ['class' => 'pagination justify-content-center mt-4'];
}
