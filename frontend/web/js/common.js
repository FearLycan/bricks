(function ($) {
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
            '  <div class="modal-content modal-loading">' +
            '    <div class="modal-header">' +
            '<h5 class="modal-title">Loading data</h5>' +
            '    </div>' +
            '    <div class="modal-body">' +
            '      <div class="ajax-container ajax-container-search loading-ajax">' +
            '        <div class="ajax-loader">' +
            '          <div class="loading-icon" role="status">' +
            '            <div class="loading-logo">' +
            '              <span></span><span></span><span></span><span></span>' +
            '            </div>' +
            '          </div>' +
            '          <div class="ajax-message">' +
            '            <p class="loading-progress">Loading content</p>' +
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
                    showFormAlert($form, 'success', data.message || 'Saved.');

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
                showFormAlert($form, 'danger', 'Could not submit form.');
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

    $(function () {
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
    });
})(jQuery);