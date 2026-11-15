<?php

/**
 * Analytics consent card (variant B: small card, bottom left).
 *
 * Shown only to visitors whose browser time zone is in Europe, where Consent
 * Mode keeps GA4 cookies off until they agree (see the gtag defaults in
 * main.php). Covers analytics only: ad consent is Google's CMP job once
 * AdSense approves the site. The choice is kept in localStorage for a year;
 * the footer link "Cookie settings" reopens the card.
 *
 * @var yii\web\View $this
 */

use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="ba-consent" id="baConsent" role="dialog" aria-live="polite"
     aria-labelledby="baConsentTitle" aria-describedby="baConsentText" hidden>
    <p class="ba-consent-title" id="baConsentTitle">
        <span class="ba-consent-icon" aria-hidden="true">🍪</span>
        <?= Html::encode(T::tr('Visit statistics')) ?>
    </p>
    <p class="ba-consent-text" id="baConsentText">
        <?= Html::encode(T::tr('We use Google Analytics cookies to see which pages and sets are useful. If you decline, the site works just the same.')) ?>
        <?= Html::a(Html::encode(T::tr('Privacy Policy')), Url::to(['/site/privacy'])) ?>
    </p>
    <div class="ba-consent-actions">
        <button type="button" class="ba-consent-btn ba-consent-btn--reject" data-consent="denied">
            <?= Html::encode(T::tr('Decline')) ?>
        </button>
        <button type="button" class="ba-consent-btn ba-consent-btn--accept" data-consent="granted">
            <?= Html::encode(T::tr('Accept')) ?>
        </button>
    </div>
</div>
<style>
    .ba-consent {
        position: fixed;
        left: 24px;
        bottom: 24px;
        z-index: 1040; /* below Bootstrap modals (1055), above sticky header (1030) */
        width: 360px;
        max-width: calc(100vw - 24px);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.22);
        padding: 18px 18px 16px;
        color: #0f172a;
    }
    .ba-consent[hidden] { display: none; }
    .ba-consent-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 6px;
        font-size: 1rem;
        font-weight: 700;
    }
    .ba-consent-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 7px;
        background: #fff4d6;
        font-size: 15px;
    }
    .ba-consent-text {
        margin: 0 0 14px;
        font-size: 0.875rem;
        line-height: 1.45;
        color: #334155;
    }
    .ba-consent-text a { color: #0b5cd6; }
    .ba-consent-actions { display: flex; gap: 8px; }
    .ba-consent-btn {
        flex: 1;
        border-radius: 999px;
        padding: 10px 16px;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f172a;
    }
    .ba-consent-btn--accept { background: #0b5cd6; border-color: #0b5cd6; color: #fff; }
    .ba-consent-btn:focus-visible { outline: 3px solid rgba(11, 92, 214, 0.4); outline-offset: 2px; }
    @media (max-width: 575.98px) {
        .ba-consent { left: 12px; right: 12px; bottom: 12px; width: auto; max-width: none; }
    }
</style>
<script>
    (function () {
        var KEY = 'ba_consent';
        var YEAR = 365 * 24 * 60 * 60 * 1000;
        var card = document.getElementById('baConsent');
        if (!card) { return; }

        function inEurope() {
            try {
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                return /^Europe\//.test(tz) || /^Atlantic\/(Canary|Madeira|Azores|Reykjavik|Faroe)$/.test(tz);
            } catch (e) {
                return true;
            }
        }

        function read() {
            try {
                var v = JSON.parse(localStorage.getItem(KEY) || 'null');
                return v && (Date.now() - (v.ts || 0)) < YEAR ? v : null;
            } catch (e) {
                return null;
            }
        }

        function save(value) {
            try { localStorage.setItem(KEY, JSON.stringify({ analytics: value, ts: Date.now() })); } catch (e) {}
            if (typeof window.gtag === 'function') {
                window.gtag('consent', 'update', { analytics_storage: value });
            }
            card.hidden = true;
        }

        card.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-consent]');
            if (btn) { save(btn.getAttribute('data-consent')); }
        });

        if (inEurope()) {
            document.querySelectorAll('[data-consent-open]').forEach(function (link) {
                link.hidden = false;
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    card.hidden = false;
                });
            });
            if (!read()) {
                card.hidden = false;
            }
        }
    })();
</script>
