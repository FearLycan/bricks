<?php

namespace frontend\components;

use Yii;

/**
 * Cheap server-side check for automated browsers.
 *
 * Used to keep Google Analytics and AdSense off requests that are clearly
 * not people: headless browsers and declared crawlers. Bot "views" of ads
 * count as invalid traffic for AdSense, and they flood GA4 with fake direct
 * sessions. A client-side check (navigator.webdriver) in the layout catches
 * headless browsers that fake their user agent.
 */
final class BotGuard
{
    private const PATTERN = '/HeadlessChrome|PhantomJS|Puppeteer|Playwright|Selenium|python-requests|curl\/|wget\/|Go-http-client|okhttp|scrapy|bot\/|bot;|\+https?:\/\/|crawler|spider|slurp|facebookexternalhit|AhrefsBot|SemrushBot|Bytespider|GPTBot|ClaudeBot|CCBot|PetalBot/i';

    private static ?bool $cached = null;

    public static function isAutomated(): bool
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $request = Yii::$app->request;
        $ua = $request instanceof \yii\web\Request ? (string)$request->getUserAgent() : '';

        return self::$cached = ($ua === '' || preg_match(self::PATTERN, $ua) === 1);
    }
}
