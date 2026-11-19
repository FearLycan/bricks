<?php

namespace frontend\components;

use yii\bootstrap5\LinkPager as BootstrapLinkPager;

class LinkPager extends BootstrapLinkPager
{
    public $maxButtonCount = 7;
    //public $firstPageLabel = '<i class="ph ph-caret-double-left"></i>';
    //public $lastPageLabel = '<i class="ph ph-caret-double-right"></i>';
    public $prevPageLabel        = '<i class="ph ph-caret-left"></i>';
    public $nextPageLabel        = '<i class="ph ph-caret-right"></i>';
    public $hideOnSinglePage     = true;
    public $linkOptions          = ['class' => 'page-link'];
    public $activePageCssClass   = 'active';
    public $disabledPageCssClass = 'disabled';
    public $options              = ['class' => 'pagination justify-content-center mt-4'];
}
