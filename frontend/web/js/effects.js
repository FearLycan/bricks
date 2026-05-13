(function () {
    'use strict';

    const reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const supportsWAAPI = typeof Element !== 'undefined'
        && typeof Element.prototype.animate === 'function';

    const HEART_PATH = 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z';

    const POP = [
        { transform: 'translateY(0) scale(1)',    offset: 0 },
        { transform: 'translateY(0) scale(0.75)', offset: 0.22 },
        { transform: 'translateY(0) scale(1.5)',  offset: 0.5 },
        { transform: 'translateY(0) scale(0.92)', offset: 0.75 },
        { transform: 'translateY(0) scale(1)',    offset: 1 },
    ];

    const POP_TUMBLE = [
        { transform: 'translateY(0) scale(1) rotate(0deg)',       offset: 0 },
        { transform: 'translateY(0) scale(0.75) rotate(-14deg)',  offset: 0.22 },
        { transform: 'translateY(0) scale(1.5) rotate(12deg)',    offset: 0.5 },
        { transform: 'translateY(0) scale(0.92) rotate(-4deg)',   offset: 0.75 },
        { transform: 'translateY(0) scale(1) rotate(0deg)',       offset: 1 },
    ];

    const POP_TIMING = {
        duration: 650,
        easing:   'cubic-bezier(0.34, 1.56, 0.64, 1)',
    };

    const PRESETS = {
        heart: {
            count:    9,
            color:    '#ef4444',
            shape:    'heart',
            spread:   70,
            jitter:   28,
            arcStart: -2.4,
            arcEnd:   2.4,
            sizeMin:  12,
            sizeMax:  22,
            pop:      POP,
        },
        box: {
            count:    10,
            color:    '#2563eb',
            shape:    'stud',
            spread:   66,
            jitter:   24,
            arcStart: -2.6,
            arcEnd:   2.6,
            sizeMin:  9,
            sizeMax:  15,
            pop:      POP_TUMBLE,
        },
    };

    function createParticleEl(preset, size) {
        const el = document.createElement('span');
        el.className = 'bx-particle bx-particle--' + preset.shape;

        if (preset.shape === 'heart') {
            el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" '
                + 'viewBox="0 0 24 24" width="' + size + '" height="' + size + '" '
                + 'fill="' + preset.color + '">'
                + '<path d="' + HEART_PATH + '"/></svg>';
            el.style.filter = 'drop-shadow(0 2px 4px rgba(239, 68, 68, 0.5))';
        } else {
            el.style.width  = size + 'px';
            el.style.height = size + 'px';
            el.style.backgroundColor = preset.color;
            el.style.borderRadius = '50%';
            el.style.boxShadow =
                'inset 0 -2px 0 rgba(0, 0, 0, 0.25),'
                + ' inset 1px 1px 0 rgba(255, 255, 255, 0.55),'
                + ' 0 0 12px rgba(37, 99, 235, 0.55)';
        }
        return el;
    }

    function spawnParticles(button, kind) {
        if (reduceMotion || !button || !supportsWAAPI) {
            return;
        }
        const preset = PRESETS[kind];
        if (!preset) {
            return;
        }

        const rect = button.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const total = preset.count;
        const arcRange = preset.arcEnd - preset.arcStart;

        for (let i = 0; i < total; i++) {
            const t = total === 1 ? 0.5 : i / (total - 1);
            const baseAngle = preset.arcStart + arcRange * t - Math.PI / 2;
            const angle = baseAngle + (Math.random() - 0.5) * 0.45;
            const distance = preset.spread + Math.random() * preset.jitter;
            const dx = Math.cos(angle) * distance;
            const dy = Math.sin(angle) * distance;
            const rot = (Math.random() - 0.5) * 360;
            const size = Math.round(preset.sizeMin + Math.random() * (preset.sizeMax - preset.sizeMin));

            const el = createParticleEl(preset, size);
            el.style.left = cx + 'px';
            el.style.top  = cy + 'px';
            document.body.appendChild(el);

            const delay = i * 18;
            const dxStr = dx.toFixed(1) + 'px';
            const dyStr = dy.toFixed(1) + 'px';
            const rotStr = rot.toFixed(0) + 'deg';

            const animation = el.animate(
                [
                    {
                        transform: 'translate(-50%, -50%) scale(0.3) rotate(0deg)',
                        opacity:   0,
                        offset:    0,
                    },
                    {
                        transform: 'translate(-50%, -50%) scale(1.25) rotate(0deg)',
                        opacity:   1,
                        offset:    0.18,
                    },
                    {
                        opacity: 0.85,
                        offset:  0.6,
                    },
                    {
                        transform: 'translate(calc(-50% + ' + dxStr + '), calc(-50% + ' + dyStr + ')) scale(0.55) rotate(' + rotStr + ')',
                        opacity:   0,
                        offset:    1,
                    },
                ],
                {
                    duration: 900,
                    delay:    delay,
                    easing:   'cubic-bezier(0.16, 1, 0.3, 1)',
                    fill:     'forwards',
                }
            );

            animation.onfinish = function () {
                el.remove();
            };
        }
    }

    function popButton(button, kind) {
        if (reduceMotion || !button || !supportsWAAPI) {
            return;
        }
        const preset = PRESETS[kind];
        if (!preset) {
            return;
        }

        button.classList.add('is-just-added');

        const anim = button.animate(preset.pop, POP_TIMING);
        anim.onfinish = function () {
            button.classList.remove('is-just-added');
        };
        anim.oncancel = function () {
            button.classList.remove('is-just-added');
        };
    }

    window.BricksFx = window.BricksFx || {};
    window.BricksFx.burst = function (button, kind) {
        popButton(button, kind);
        spawnParticles(button, kind);
    };
})();
