/**
 * GA4 "offer_click": fired when a visitor opens a store offer (affiliate link)
 * on a set page. Lets us see which pages, sets and stores lead to store visits.
 *
 * gtag only exists when tags are loaded (not for bots, see BotGuard) and it
 * honours Consent Mode itself, so nothing here needs to check consent.
 * transport_type "beacon" keeps the hit alive while the store opens in a new tab.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a.lego-offer-action') : null;
        if (!link || typeof window.gtag !== 'function') {
            return;
        }

        var params = {
            store: link.dataset.offerStore || '(unknown)',
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
    }, true);
})();
