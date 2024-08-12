<?php

namespace common\components;

use yii\helpers\Url;
use Yii;

class Html extends \yii\helpers\Html
{
    public static function img($src, $options = [])
    {
        $options['src'] = Url::to($src);

        if (Yii::$app->components['assetManager']['appendTimestamp'] && !str_contains($src, 'http')) {
            $dstFile = Yii::getAlias('@app/web') . $options['src'];
            if (($timestamp = @filemtime($dstFile)) > 0) {
                $options['src'] = Url::to($src) . '?v=' . $timestamp;
            }
        }

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