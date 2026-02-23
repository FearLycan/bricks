<?php

namespace frontend\components;

use Yii;

class T
{
    public static function t(string $message, array $params = [], string $category = 'app', ?string $language = null): string
    {
        return Yii::t($category, $message, $params, $language);
    }
}