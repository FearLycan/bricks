(function ($) {
    'use strict';

    const getCsrfToken = function () {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    const getCsrfParam = function () {
        const meta = document.querySelector('meta[name="csrf-param"]');
        return meta ? meta.getAttribute('content') : '_csrf';
    };

    const updateHeartButton = function ($button, inWishlist) {
        $button
            .toggleClass('is-active', !!inWishlist)
            .attr('aria-pressed', inWishlist ? 'true' : 'false');

        const $icon = $button.find('i');
        if (inWishlist) {
            $icon.removeClass('bi-heart').addClass('bi-heart-fill');
            $button.attr('title', $button.data('label-remove') || 'Remove from wishlist');
        } else {
            $icon.removeClass('bi-heart-fill').addClass('bi-heart');
            $button.attr('title', $button.data('label-add') || 'Add to wishlist');
        }
    };

    $(document).on('click', '.js-wishlist-toggle', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const $button = $(this);
        if ($button.hasClass('is-loading')) {
            return;
        }

        const url = $button.data('toggle-url');
        const setId = parseInt($button.data('set-id'), 10);
        if (!url || !setId) {
            return;
        }

        $button.addClass('is-loading');

        const payload = {
            set_id: setId,
        };
        payload[getCsrfParam()] = getCsrfToken();

        $.ajax({
            url: url,
            method: 'POST',
            data: payload,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            success: function (data) {
                if (data && data.success) {
                    updateHeartButton($button, !!data.in_wishlist);

                    $('.js-wishlist-toggle[data-set-id="' + setId + '"]').each(function () {
                        const $other = $(this);
                        if ($other.is($button)) {
                            return;
                        }
                        updateHeartButton($other, !!data.in_wishlist);
                    });

                    if (data.in_wishlist && window.BricksFx) {
                        window.BricksFx.burst($button[0], 'heart');
                    }
                }
            },
            complete: function () {
                $button.removeClass('is-loading');
            },
        });
    });

    $(document).on('click', '.js-wishlist-remove', function (event) {
        event.preventDefault();

        const $button = $(this);
        if ($button.is(':disabled')) {
            return;
        }

        const url = $button.data('remove-url');
        const setId = parseInt($button.data('set-id'), 10);
        if (!url || !setId) {
            return;
        }

        const $item = $button.closest('.js-wishlist-item');
        $item.addClass('is-removing');
        $button.prop('disabled', true);

        const payload = {
            set_id: setId,
        };
        payload[getCsrfParam()] = getCsrfToken();

        $.ajax({
            url: url,
            method: 'POST',
            data: payload,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            success: function (data) {
                if (data && data.success) {
                    $item.slideUp(180, function () {
                        $item.remove();

                        $('.js-wishlist-toggle[data-set-id="' + setId + '"]').each(function () {
                            updateHeartButton($(this), false);
                        });

                        const $list = $('.js-wishlist-list');
                        if ($list.length && !$list.children('.js-wishlist-item').length) {
                            $('.js-wishlist-empty-template').removeClass('d-none');
                            $list.addClass('d-none');
                        }
                    });
                } else {
                    $item.removeClass('is-removing');
                    $button.prop('disabled', false);
                }
            },
            error: function () {
                $item.removeClass('is-removing');
                $button.prop('disabled', false);
            },
        });
    });
})(jQuery);
