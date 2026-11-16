<?php

/**
 * Language suggestion card (variant B: bottom right).
 *
 * Automatic Accept-Language redirects are off (urlManager
 * enableLanguageDetection = false), so every URL serves the language in its
 * path. When the browser prefers another supported language, this card offers
 * a switch, written in THAT language. Shown once: "No thanks", "Switch" or any
 * use of the language switcher stores the choice in localStorage for a year.
 * Waits until no consent UI is pending (see _consent-card.php) so cards never stack.
 *
 * @var yii\web\View $this
 */

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Json;

$current = (string)Yii::$app->language;
$offers = [];
foreach (SeoHelper::SUPPORTED_LANGUAGES as $lang) {
    if ($lang === $current) {
        continue;
    }
    // English is the source language, so Yii returns source strings for it
    // untranslated; spell out the English wording here.
    $offers[$lang] = [
        'url'    => SeoHelper::buildCurrentUrlInLanguage($lang),
        'title'  => $lang === 'en' ? 'Available in English' : T::tr('Available in your language', [], 'app', $lang),
        'text'   => $lang === 'en'
            ? 'BrickAtlas is also available in English: prices, gift guides and the set finder.'
            : T::tr('BrickAtlas is also available in your language: prices, gift guides and the set finder.', [], 'app', $lang),
        'switch' => T::tr('Switch', [], 'app', $lang),
        'stay'   => T::tr('No thanks', [], 'app', $lang),
        'flag'   => Yii::getAlias('@web/images/flags/' . $lang . '.svg'),
    ];
}
?>
<div class="ba-lang" id="baLang" role="dialog" aria-live="polite" aria-labelledby="baLangTitle" hidden>
    <p class="ba-lang-title" id="baLangTitle">
        <img class="ba-lang-flag" src="" alt="" width="22" height="15">
        <span></span>
    </p>
    <p class="ba-lang-text"></p>
    <div class="ba-lang-actions">
        <button type="button" class="ba-lang-btn ba-lang-btn--stay" data-lang-stay></button>
        <a class="ba-lang-btn ba-lang-btn--switch" href="#" data-lang-switch></a>
    </div>
</div>
<style>
    .ba-lang {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1040; /* same layer as the consent card (bottom left) */
        width: 330px;
        max-width: calc(100vw - 24px);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.22);
        padding: 16px;
        color: #0f172a;
    }
    .ba-lang[hidden] { display: none; }
    .ba-lang-title { display: flex; align-items: center; gap: 8px; margin: 0 0 6px; font-size: 1rem; font-weight: 700; }
    .ba-lang-flag { border-radius: 2px; }
    .ba-lang-text { margin: 0 0 12px; font-size: 0.875rem; line-height: 1.45; color: #334155; }
    .ba-lang-actions { display: flex; gap: 8px; }
    .ba-lang-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.1;
        text-decoration: none;
        cursor: pointer;
    }
    .ba-lang-btn--stay { background: #f1f3f9; border: 1px solid #e1e5f0; color: #1b1f3b; font-weight: 600; }
    .ba-lang-btn--switch { background: #d01012; border: 1px solid #d01012; color: #fff; }
    .ba-lang-btn--switch:hover, .ba-lang-btn--switch:focus-visible { background: #b00d0f; color: #fff; }
    .ba-lang-btn:focus-visible { outline: 3px solid rgba(11, 92, 214, 0.4); outline-offset: 2px; }
    @media (max-width: 575.98px) {
        .ba-lang { left: 12px; right: 12px; bottom: 12px; width: auto; max-width: none; }
    }
</style>
<script>
    (function () {
        var KEY = 'ba_lang_choice';
        var YEAR = 365 * 24 * 60 * 60 * 1000;
        var CURRENT = <?= Json::htmlEncode($current) ?>;
        var OFFERS = <?= Json::htmlEncode($offers) ?>;
        var card = document.getElementById('baLang');
        if (!card) { return; }

        function decided() {
            try {
                var v = JSON.parse(localStorage.getItem(KEY) || 'null');
                return !!(v && (Date.now() - (v.ts || 0)) < YEAR);
            } catch (e) {
                return true; // no storage: never nag
            }
        }

        function remember(choice) {
            try { localStorage.setItem(KEY, JSON.stringify({ choice: choice, ts: Date.now() })); } catch (e) {}
        }

        // Any use of the language switcher counts as a decision.
        document.querySelectorAll('.bricks-language-switcher .dropdown-item').forEach(function (a) {
            a.addEventListener('click', function () { remember('switcher'); });
        });

        if (decided()) { return; }

        // First supported language in the browser's preference list.
        var prefs = navigator.languages && navigator.languages.length ? navigator.languages : [navigator.language || ''];
        var target = null;
        for (var i = 0; i < prefs.length; i++) {
            var code = String(prefs[i] || '').toLowerCase().split('-')[0];
            if (code === CURRENT) { return; }      // already in a preferred language
            if (OFFERS[code]) { target = code; break; }
        }
        if (!target) { return; }

        var o = OFFERS[target];
        card.setAttribute('lang', target);
        card.querySelector('.ba-lang-flag').src = o.flag;
        card.querySelector('.ba-lang-title span').textContent = o.title;
        card.querySelector('.ba-lang-text').textContent = o.text;
        var stay = card.querySelector('[data-lang-stay]');
        var sw = card.querySelector('[data-lang-switch]');
        stay.textContent = o.stay;
        sw.textContent = o.switch;
        sw.href = o.url;
        sw.setAttribute('hreflang', target);

        stay.addEventListener('click', function () { remember('stay:' + CURRENT); card.hidden = true; });
        sw.addEventListener('click', function () { remember('switch:' + target); });

        // Do not stack with a consent UI (ours or Google's): wait until the
        // consent card script reports that nothing is pending.
        function show() { card.hidden = false; }
        if (!document.getElementById('baConsent') || window.baConsentSettled) {
            setTimeout(show, 1200);
        } else {
            document.addEventListener('ba:consent-settled', function () { setTimeout(show, 400); }, { once: true });
        }
    })();
</script>
