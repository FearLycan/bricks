<?php

namespace common\components;

use yii\helpers\Url;

class Html extends \yii\helpers\Html
{
    public static function img($src, $options = [])
    {
        $options['src'] = Url::to($src);

        if (isset($options['srcset']) && is_array($options['srcset'])) {
            $srcset = [];
            foreach ($options['srcset'] as $descriptor => $url) {
                $srcset[] = Url::to($url) . ' ' . $descriptor;
            }
            $options['srcset'] = implode(',', $srcset);
        }

        if (!isset($options['loading'])) {
            $options['loading'] = 'lazy';
        }

        if (!isset($options['alt'])) {
            $options['alt'] = '';
        }

        return static::tag('img', '', $options);
    }
}