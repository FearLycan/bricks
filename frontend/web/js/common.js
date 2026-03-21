(() => {
    const smoothScrollTo = (targetY, duration = 450) => {
        const startY = window.scrollY;
        const distance = targetY - startY;
        let startTime = null;

        const step = (timestamp) => {
            if (startTime === null) {
                startTime = timestamp;
            }

            const progress = Math.min((timestamp - startTime) / duration, 1);
            const eased = progress < 0.5
                ? 2 * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 2) / 2;

            window.scrollTo(0, startY + distance * eased);

            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };

        window.requestAnimationFrame(step);
    };

    const handleSmoothScrollEvent = (event, duration = 450) => {
        const eventTarget = event.target;
        const target = eventTarget instanceof Element ? eventTarget : eventTarget?.parentElement;
        if (!(target instanceof Element)) {
            return;
        }

        const link = target.closest('a.js-smooth-scroll[data-scroll-target]');
        if (!(link instanceof HTMLAnchorElement)) {
            return;
        }

        const selector = link.dataset.scrollTarget || '';
        if (!selector.startsWith('#')) {
            return;
        }

        const section = document.querySelector(selector);
        if (!section) {
            return;
        }

        event.preventDefault();
        smoothScrollTo(section.getBoundingClientRect().top + window.scrollY, duration);
        history.replaceState(null, '', selector);
    };

    document.addEventListener('pointerup', (event) => {
        if (event.pointerType === 'mouse') {
            return;
        }

        handleSmoothScrollEvent(event, 220);
    }, true);

    document.addEventListener('click', handleSmoothScrollEvent, true);
})();
