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

    const updateBoxButton = function ($button, isOwned) {
        $button
            .toggleClass('is-active', !!isOwned)
            .attr('aria-pressed', isOwned ? 'true' : 'false');

        const $icon = $button.find('i');
        if (isOwned) {
            $icon.removeClass('bi-box-seam').addClass('bi-box-seam-fill');
            $button.attr('title', $button.data('label-remove') || 'Remove from owned sets');
        } else {
            $icon.removeClass('bi-box-seam-fill').addClass('bi-box-seam');
            $button.attr('title', $button.data('label-add') || 'Add to owned sets');
        }
    };

    $(document).on('click', '.js-owned-set-toggle', function (event) {
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
                    updateBoxButton($button, !!data.is_owned);

                    $('.js-owned-set-toggle[data-set-id="' + setId + '"]').each(function () {
                        const $other = $(this);
                        if ($other.is($button)) {
                            return;
                        }
                        updateBoxButton($other, !!data.is_owned);
                    });
                }
            },
            complete: function () {
                $button.removeClass('is-loading');
            },
        });
    });

    $(document).on('click', '.js-owned-set-remove', function (event) {
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

        const $item = $button.closest('.js-owned-set-item');
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

                        $('.js-owned-set-toggle[data-set-id="' + setId + '"]').each(function () {
                            updateBoxButton($(this), false);
                        });

                        const $list = $('.js-owned-set-list');
                        if ($list.length && !$list.children('.js-owned-set-item').length) {
                            $('.js-owned-set-empty-template').removeClass('d-none');
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
