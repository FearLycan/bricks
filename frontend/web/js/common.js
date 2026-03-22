(function ($) {
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
})(jQuery);