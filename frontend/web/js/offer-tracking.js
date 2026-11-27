/**
 * Store link clicks on set pages:
 *  - our own count: POST /click with navigator.sendBeacon (frontend\controllers\ClickController),
 *    so only browsers that run this script are counted;
 *  - GA4 "offer_click", when gtag is loaded (not for bots, see BotGuard; it honours Consent
 *    Mode itself). transport_type "beacon" keeps the hit alive while the store opens.
 *
 * Links: a.lego-offer-action (data-offer-id, store offers) and a.lego-official-price
 * (data-set-id, lego.com search).
 */
(function () {
    'use strict';

    var meta = function (name) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el ? el.getAttribute('content') : '';
    };

    var beacon = function (kind, id) {
        if (!id || !navigator.sendBeacon) {
            return;
        }
        var data = new FormData();
        data.append('kind', kind);
        data.append('id', id);
        data.append('lang', document.documentElement.lang || '');
        data.append(meta('csrf-param') || '_csrf-frontend', meta('csrf-token'));
        try {
            navigator.sendBeacon('/click', data);
        } catch (e) {
            // counting must never break the link
        }
    };

    var track = function (e) {
        if (!e.target.closest) {
            return;
        }
        var link = e.target.closest('a.lego-offer-action, a.lego-official-price');
        if (!link) {
            return;
        }
        var official = link.classList.contains('lego-official-price');
        beacon(official ? 'lego' : 'offer', official ? link.dataset.setId : link.dataset.offerId);

        if (typeof window.gtag !== 'function') {
            return;
        }
        var params = {
            store: official ? 'LEGO.com' : (link.dataset.offerStore || '(unknown)'),
            set_number: link.dataset.offerSet || '',
            link_domain: link.hostname || '',
            transport_type: 'beacon'
        };
        var price = parseFloat(link.dataset.offerPrice || '');
        if (!isNaN(price) && price > 0) {
            params.value = price;
            params.currency = link.dataset.offerCurrency || 'USD';
        }
        window.gtag('event', 'offer_click', params);
    };

    // click covers left click and keyboard; auxclick covers middle click (new tab).
    document.addEventListener('click', track, true);
    document.addEventListener('auxclick', function (e) {
        if (e.button === 1) {
            track(e);
        }
    }, true);
})();
