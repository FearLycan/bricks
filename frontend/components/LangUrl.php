<?php

namespace frontend\components;

use Yii;
use yii\helpers\Url;

/**
 * Path URL in the current language.
 *
 * Url::to('/lego/foo') with a plain string is NOT prefixed by
 * codemix\localeurls, so on /pl/ pages it pointed at the English URL
 * (/lego/foo): crawlers followed PL pages to EN pages and visitors with a
 * language cookie got an extra redirect. Use this for hand-built paths.
 */
final class LangUrl
{
    public static function to(string $path, bool|string $scheme = false): string
    {
        $path = '/' . ltrim($path, '/');
        $language = Yii::$app->language;
        $default = Yii::$app->urlManager->languages[0] ?? 'en';
        if ($language !== '' && $language !== $default && !str_starts_with($path, '/' . $language . '/')) {
            $path = '/' . $language . ($path === '/' ? '/' : $path);
        }

        return Url::to($path, $scheme);
    }
}
