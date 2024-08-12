<?php

namespace frontend\components;

use common\components\Html;

class Helper
{
    public static function getLegoName(): string
    {
        return Html::encode('LEGO') . '<sup>®</sup>';
    }
}