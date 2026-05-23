(function ($) {
    const t = function (key, fallback) {
        const value = document && document.body && document.body.dataset
            ? document.body.dataset[key]
            : '';
        return value && value.length ? value : fallback;
    };

    const getOrCreateModalTarget = function (target) {
        const selector = target || '#mainModal';
        let $target = $(selector);
        if ($target.length) {
            return $target;
        }

        if (!selector.startsWith('#')) {
            return $target;
        }

        const modalId = selector.slice(1);
        if (!modalId) {
            return $target;
        }

        $('body').append('<div class="modal fade" id="' + modalId + '" tabindex="-1" aria-hidden="true"></div>');
        $target = $(selector);

        return $target;
    };

    const normalizeForce = function (value) {
        if (typeof value === 'string') {
            return value === '1' || value === 'true';
        }

        return Boolean(value);
    };

    const closeModalByTarget = function (target) {
        const selector = target || '#mainModal';
        const modalElement = document.querySelector(selector);
        if (!modalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
    };

    const handleModalFormSubmit = function (formElement) {
        const $form = $(formElement);
        if (!$form.length) {
            return;
        }

        const modalTarget = $form.data('modal-target') || '#mainModal';
        const closeOnSuccessRaw = $form.data('close-on-success');
        const closeOnSuccess = closeOnSuccessRaw === undefined ? true : normalizeForce(closeOnSuccessRaw);

        window.sendModal(formElement, modalTarget, function () {
            if (!closeOnSuccess) {
                return;
            }

            closeModalByTarget(modalTarget);
        });
    };

    const showFormAlert = function ($form, type, message) {
        const $alert = $form.find('.js-form-alert');
        if (!$alert.length) {
            return;
        }

        $alert
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert alert-' + type)
            .text(message);
    };

    const clearFormValidation = function ($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.js-field-error').addClass('d-none').text('');
        const $alert = $form.find('.js-form-alert');
        if ($alert.length) {
            $alert.addClass('d-none').removeClass('alert-success alert-danger').text('');
        }
    };

    const applyFormErrors = function ($form, errors) {
        if (!errors || typeof errors !== 'object') {
            return;
        }

        Object.keys(errors).forEach(function (attribute) {
            const messages = errors[attribute];
            if (!Array.isArray(messages) || !messages.length) {
                return;
            }

            const $input = $form.find('[name$="[' + attribute + ']"], [name="' + attribute + '"]').first();
            if ($input.length) {
                $input.addClass('is-invalid');
            }

            const $errorNode = $form.find('.js-field-error[data-field="' + attribute + '"]').first();
            if ($errorNode.length) {
                $errorNode.removeClass('d-none').text(messages[0]);
            }
        });
    };

    const buildModalLoadingHtml = function () {
        return '' +
            '<div class="modal-dialog modal-dialog-centered modal-dialog-loading">' +
            '  <div class="modal-content modal-loading modal-loading-skeleton">' +
            '    <div class="modal-header">' +
            '<h5 class="modal-title">' + t('i18nLoading', 'Loading data') + '</h5>' +
            '    </div>' +
            '    <div class="modal-body">' +
            '      <div class="modal-loading-lines mb-3">' +
            '        <div class="modal-loading-line modal-loading-line-lg"></div>' +
            '        <div class="modal-loading-line"></div>' +
            '        <div class="modal-loading-line modal-loading-line-sm"></div>' +
            '      </div>' +
            '      <div class="ajax-container ajax-container-search loading-ajax">' +
            '        <div class="ajax-loader">' +
            '          <div class="loading-icon" role="status">' +
            '            <div class="loading-logo">' +
            '              <span></span><span></span><span></span><span></span>' +
            '            </div>' +
            '          </div>' +
            '          <div class="ajax-message">' +
            '            <p class="loading-progress"></p>' +
            '          </div>' +
            '        </div>' +
            '      </div>' +
            '    </div>' +
            '    <div class="modal-footer"></div>' +
            '  </div>' +
            '</div>';
    };

    window.loadModal = function (target, url, force = false) {
        const $target = getOrCreateModalTarget(target);
        if (!$target.length || !url) {
            return;
        }

        const forceModal = normalizeForce(force);
        const modalElement = $target.get(0);
        if (!modalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }

        $target.html(buildModalLoadingHtml());
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
            show: true,
            keyboard: !forceModal,
            backdrop: forceModal ? 'static' : true,
        });

        $.ajax({
            url: url,
            dataType: 'html',
            success: function (data) {
                if (data === 'OK') {
                    modal.hide();
                } else {
                    $target.html(data);
                    const $dialog = $target.children('.modal-dialog').first();
                    if ($dialog.length) {
                        $dialog.addClass('modal-content-fade-in');
                        window.setTimeout(function () {
                            $dialog.removeClass('modal-content-fade-in');
                        }, 260);
                    }
                }

            },
            complete: function () {
                if (typeof window.loadfields === 'function') {
                    window.loadfields();
                }

                if (typeof window.loadDateTimePickers === 'function') {
                    window.loadDateTimePickers();
                }
            },
        });

        modal.show();
    };

    window.sendModal = function (formElement, target, callback) {
        const $form = $(formElement);
        if (!$form.length) {
            return;
        }

        const actionUrl = $form.attr('action') || window.location.href;
        const $submitButton = $form.find('.js-submit-btn');
        if ($submitButton.length) {
            $submitButton.prop('disabled', true);
        }

        clearFormValidation($form);

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            success: function (data) {
                if (data && data.success) {
                    showFormAlert($form, 'success', data.message || t('i18nSaved', 'Saved.'));

                    if (typeof callback === 'function') {
                        callback();
                    }
                    return;
                }

                applyFormErrors($form, data && data.errors ? data.errors : {});

                if (data && data.message) {
                    showFormAlert($form, 'danger', data.message);
                }

            },
            error: function () {
                showFormAlert($form, 'danger', t('i18nSubmitError', 'Could not submit form.'));
            },
            complete: function () {
                if ($submitButton.length) {
                    $submitButton.prop('disabled', false);
                }
            },
        });
    };

    $('a.js-smooth-scroll[href*="#"]')
        .not('[href="#"]')
        .not('[href="#0"]')
        .on('click', function (event) {
            if (
                location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') &&
                location.hostname === this.hostname
            ) {
                let target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

                if (target.length) {
                    event.preventDefault();
                    let menuHeight = $('#menu-navbar').outerHeight() + 1;
                    $('html, body').animate({scrollTop: target.offset().top - menuHeight}, 700,
                        function () {
                            var $target = target;
                            $target.focus();

                            if ($target.is(':focus')) {
                                return false;
                            }

                            $target.attr('tabindex', '-1');
                            $target.focus();
                        }
                    );
                }
            }
        });

    const initFlashToasts = function () {
        if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
            return;
        }

        document.querySelectorAll('.bx-toast-container .toast').forEach(function (toastEl) {
            const delay = parseInt(toastEl.getAttribute('data-bs-delay'), 10) || 5000;
            bootstrap.Toast.getOrCreateInstance(toastEl, {
                autohide: toastEl.getAttribute('data-bs-autohide') !== 'false',
                delay: delay,
            }).show();
        });
    };

    $(function () {
        initFlashToasts();

        $(document).on('click', '.js-load-modal', function (event) {
            event.stopPropagation();
            event.preventDefault();

            const $element = $(this);
            const url = $element.attr('href') || $element.data('modal-url') || '';
            if (!url || url === '#') {
                return;
            }

            const target = $element.data('target') || '#mainModal';
            const force = $element.data('force') || false;

            window.loadModal(target, url, force);
        });

        $(document).on('click', '.js-submit-modal-form .js-submit-btn', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const formElement = this.form || $(this).closest('form').get(0);
            if (!formElement) {
                return;
            }

            handleModalFormSubmit(formElement);
        });

        $(document).on('submit', '.js-submit-modal-form', function (event) {
            event.preventDefault();
            handleModalFormSubmit(this);

            return false;
        });

        // When loadModal swaps content while a modal is shown (e.g. choice → detailed
        // review), Bootstrap can leave orphan .modal-backdrop nodes and keep
        // body.modal-open + inline padding-right after the modal eventually closes,
        // which freezes page scrolling. After any modal hides, force-clean body state
        // unless another modal is still visible.
        $(document).on('hidden.bs.modal', '.modal', function () {
            if (document.querySelector('.modal.show')) {
                return;
            }
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        });
    });

    initVenoBox();
})(jQuery);

function initVenoBox(customOptions = {}) {
    const defaultOptions = {
        selector: '.venobox',
        numeration: true,
        infinigall: true,
        share: false,
        spinner: 'rotating-plane',
        titleattr: 'data-title',
        titlePosition: 'bottom',
        titleStyle: 'bar',
    };

    const options = {...defaultOptions, ...customOptions};

    return new VenoBox(options);
}
/* ==========================================================================
   Horizontal slider navigation.
   Binds prev/next chevron buttons rendered by _section-slider.php (and any
   other element marked [data-bricks-slider]) to scroll their track by roughly
   one card width per click. Buttons stay hidden until the track actually
   overflows so static lists never get useless controls.
   ========================================================================== */
(function () {
    function initBricksSlider(slider) {
        const track = slider.querySelector('[data-bricks-slider-track]');
        const prev  = slider.querySelector('[data-bricks-slider-prev]');
        const next  = slider.querySelector('[data-bricks-slider-next]');
        if (!track) {
            return;
        }

        function stepSize() {
            const card = track.querySelector('.bricks-slider-item');
            if (!card) {
                return track.clientWidth * 0.8;
            }
            const styles = window.getComputedStyle(track);
            const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
            return Math.max(120, card.getBoundingClientRect().width + gap);
        }

        function refreshButtons() {
            const hasOverflow = track.scrollWidth - track.clientWidth > 1;
            if (prev) {
                prev.hidden = !hasOverflow;
                prev.disabled = track.scrollLeft <= 1;
            }
            if (next) {
                next.hidden = !hasOverflow;
                next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
            }
        }

        if (prev) {
            prev.addEventListener('click', function () {
                track.scrollBy({left: -stepSize(), behavior: 'smooth'});
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                track.scrollBy({left: stepSize(), behavior: 'smooth'});
            });
        }

        track.addEventListener('scroll', refreshButtons, {passive: true});
        window.addEventListener('resize', refreshButtons);
        refreshButtons();

        // Card images load lazily — sizes change after first paint.
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(refreshButtons).observe(track);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bricks-slider]').forEach(initBricksSlider);
    });
})();

/* ==========================================================================
   Floating "back to top" button.
   Shows after the user scrolls past one viewport; click triggers a custom
   ease-out-quart smooth-scroll for a snappier feel than the browser default.
   Respects prefers-reduced-motion by jumping instantly.
   ========================================================================== */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('scrollToTopBtn');
        if (!btn) {
            return;
        }

        var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var revealThreshold = Math.max(320, Math.round(window.innerHeight * 0.6));

        function updateVisibility() {
            var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
            btn.classList.toggle('is-visible', scrollY > revealThreshold);
        }

        // ease-out-quart: starts fast, decelerates smoothly to a stop.
        function easeOutQuart(t) {
            return 1 - Math.pow(1 - t, 4);
        }

        function animateScrollToTop() {
            if (prefersReducedMotion) {
                window.scrollTo(0, 0);
                return;
            }

            var startY = window.pageYOffset || document.documentElement.scrollTop || 0;
            if (startY <= 0) {
                return;
            }

            // Longer scrolls get a slightly longer animation, capped so it never drags.
            var duration = Math.min(900, Math.max(450, startY * 0.45));
            var startTime = null;

            btn.classList.add('is-scrolling');
            setTimeout(function () { btn.classList.remove('is-scrolling'); }, 750);

            function step(timestamp) {
                if (startTime === null) { startTime = timestamp; }
                var elapsed = timestamp - startTime;
                var progress = Math.min(1, elapsed / duration);
                var eased = easeOutQuart(progress);
                window.scrollTo(0, Math.round(startY * (1 - eased)));
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            }
            window.requestAnimationFrame(step);
        }

        window.addEventListener('scroll', updateVisibility, {passive: true});
        window.addEventListener('resize', function () {
            revealThreshold = Math.max(320, Math.round(window.innerHeight * 0.6));
            updateVisibility();
        });
        btn.addEventListener('click', animateScrollToTop);

        updateVisibility();
    });
})();
