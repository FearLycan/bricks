(function () {
    'use strict';

    // ─── Helpers ──────────────────────────────────────────────────────────────

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    }

    function readI18n(root) {
        if (!root) return {};
        try {
            return JSON.parse(root.dataset.reviewI18n || '{}');
        } catch (_) {
            return {};
        }
    }

    function showAlert(root, message) {
        if (!root) return;
        const alert = root.querySelector('.js-form-alert');
        if (!alert) return;
        alert.classList.remove('d-none');
        alert.textContent = message;
    }

    function hideAlert(root) {
        if (!root) return;
        const alert = root.querySelector('.js-form-alert');
        if (!alert) return;
        alert.classList.add('d-none');
        alert.textContent = '';
    }

    function updateStars(starsEl, score) {
        if (!starsEl) return;
        const value = Math.max(0, Math.min(10, Number(score) || 0)) / 2;
        const stars = starsEl.querySelectorAll('.review-star i');
        stars.forEach(function (icon, idx) {
            const starIndex = idx + 1;
            icon.classList.remove('bi-star', 'bi-star-half', 'bi-star-fill');
            if (value >= starIndex) {
                icon.classList.add('bi-star-fill');
            } else if (value >= starIndex - 0.5) {
                icon.classList.add('bi-star-half');
            } else {
                icon.classList.add('bi-star');
            }
        });
    }

    function bindSlider(scope) {
        scope.querySelectorAll('[data-role="score-slider"]').forEach(function (slider) {
            const block = slider.closest('.review-slider-block') || scope;
            const valueEl = block.querySelector('[data-role="score-value"]');
            const starsEl = block.querySelector('[data-role="score-stars"]');

            const render = function () {
                const value = Number(slider.value || 0);
                if (valueEl) {
                    valueEl.textContent = value.toFixed(2);
                }
                updateStars(starsEl, value);
            };

            slider.addEventListener('input', render);
            slider.addEventListener('change', render);
            render();
        });
    }

    function getRedirectStrategy(data) {
        if (data && typeof data === 'object' && data.redirectUrl) {
            return data.redirectUrl;
        }
        return null;
    }

    function reloadPage(targetUrl) {
        if (!targetUrl) {
            window.location.reload();
            return;
        }
        try {
            const newUrl = new URL(targetUrl, window.location.href);
            const samePage =
                newUrl.origin === window.location.origin &&
                newUrl.pathname === window.location.pathname &&
                newUrl.search === window.location.search;
            if (samePage) {
                // Setting location.href to the same path-with-different-hash only
                // updates the anchor — it does not reload. After a save we always
                // want fresh server-rendered stats, so force a reload.
                if (newUrl.hash && newUrl.hash !== window.location.hash) {
                    window.location.hash = newUrl.hash;
                }
                window.location.reload();
                return;
            }
        } catch (_) {
            // fall through to plain assignment
        }
        window.location.href = targetUrl;
    }

    // ─── Simple form ──────────────────────────────────────────────────────────

    function bindSimpleForm(formEl) {
        if (!formEl || formEl.dataset.reviewBound === '1') {
            return;
        }
        formEl.dataset.reviewBound = '1';

        const root = formEl.closest('.review-modal') || formEl;
        const i18n = readI18n(root);

        bindSlider(formEl);

        const submitBtn = formEl.querySelector('[data-role="submit"]');
        const originalLabel = submitBtn ? submitBtn.innerHTML : '';

        formEl.addEventListener('submit', function (event) {
            event.preventDefault();
            hideAlert(formEl);

            const slider = formEl.querySelector('[data-role="score-slider"]');
            const titleEl = formEl.querySelector('[data-role="title"]');
            const contentEl = formEl.querySelector('[data-role="content"]');
            const ownsEl = formEl.querySelector('[data-role="owns-set"]');

            const payload = {
                set_id: Number(formEl.dataset.setId || 0),
                overall_score: slider ? Number(slider.value) : null,
                title: titleEl ? titleEl.value : '',
                content: contentEl ? contentEl.value : '',
                owns_set: ownsEl ? Boolean(ownsEl.checked) : false,
            };

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (i18n.saving || 'Saving...');
            }

            fetch(formEl.dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.data && result.data.success) {
                        reloadPage(getRedirectStrategy(result.data));
                        return;
                    }
                    const message = (result.data && result.data.message) || i18n.errorGeneric || 'An error occurred. Please try again.';
                    showAlert(formEl, message);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalLabel;
                    }
                })
                .catch(function () {
                    showAlert(formEl, i18n.errorConnection || 'Connection error. Check your internet and try again.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalLabel;
                    }
                });
        });
    }

    // ─── Detailed wizard ──────────────────────────────────────────────────────

    function bindDetailedWizard(rootEl) {
        if (!rootEl || rootEl.dataset.reviewBound === '1') {
            return;
        }
        rootEl.dataset.reviewBound = '1';

        const i18n = readI18n(rootEl);
        const totalSteps = Number(rootEl.dataset.totalSteps || 0);
        const stepsOrderInput = rootEl.querySelector('[data-role="steps-order"]');
        const stepTitlesInput = rootEl.querySelector('[data-role="step-titles"]');
        const ownsSetInput = rootEl.querySelector('[data-role="owns-set"]');
        const titleEl = rootEl.querySelector('.review-step[data-step="summary"] [data-role="title"]');
        const contentEl = rootEl.querySelector('.review-step[data-step="summary"] [data-role="content"]');
        const stepTitleNode = rootEl.querySelector('[data-role="step-title"]');
        const stepIndicator = rootEl.querySelector('[data-role="step-indicator"]');
        const progressBar = rootEl.querySelector('[data-role="progress-bar"]');
        const backBtn = rootEl.querySelector('[data-role="back"]');
        const nextBtn = rootEl.querySelector('[data-role="next"]');
        const submitBtn = rootEl.querySelector('[data-role="submit"]');
        const overallDisplay = rootEl.querySelector('[data-role="overall-display"]');
        const overallStars = rootEl.querySelector('[data-role="overall-stars"]');
        const choiceSwitchBtn = rootEl.querySelector('[data-role="switch-simple"]');

        let stepsOrder = [];
        try {
            stepsOrder = JSON.parse(stepsOrderInput?.value || '[]');
        } catch (_) {
            stepsOrder = [];
        }

        let stepTitles = {};
        try {
            stepTitles = JSON.parse(stepTitlesInput?.value || '{}');
        } catch (_) {
            stepTitles = {};
        }

        let currentIndex = 0;
        const answers = {};
        const preferences = {};

        // Initialise existing answers (already selected via PHP rendering)
        rootEl.querySelectorAll('.review-q-option.review-q-option--selected').forEach(function (btn) {
            answers[btn.dataset.questionKey] = btn.dataset.value;
        });
        rootEl.querySelectorAll('.review-pref-option--selected').forEach(function (btn) {
            const key = btn.dataset.prefKey;
            if (!preferences[key]) {
                preferences[key] = [];
            }
            preferences[key].push(btn.dataset.value);
        });

        bindSlider(rootEl);

        // Single-select for radio-style question buttons
        rootEl.querySelectorAll('[data-role="answer-option"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const key = btn.dataset.questionKey;
                rootEl.querySelectorAll('[data-role="answer-option"][data-question-key="' + key + '"]').forEach(function (other) {
                    other.classList.remove('review-q-option--selected');
                });
                btn.classList.add('review-q-option--selected');
                answers[key] = btn.dataset.value;
            });
        });

        // Text answers
        rootEl.querySelectorAll('textarea[data-role="answer"]').forEach(function (textarea) {
            textarea.addEventListener('input', function () {
                const key = textarea.dataset.questionKey;
                const value = textarea.value.trim();
                if (value) {
                    answers[key] = value;
                } else {
                    delete answers[key];
                }
            });
        });

        // Preference multi-select
        rootEl.querySelectorAll('[data-role="pref-option"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const key = btn.dataset.prefKey;
                const value = btn.dataset.value;
                const block = btn.closest('.review-pref-block');
                const maxSelect = block ? Number(block.dataset.maxSelect || 0) : 0;

                if (!preferences[key]) {
                    preferences[key] = [];
                }

                const idx = preferences[key].indexOf(value);
                if (idx >= 0) {
                    preferences[key].splice(idx, 1);
                    btn.classList.remove('review-pref-option--selected');
                    return;
                }

                if (maxSelect > 0 && preferences[key].length >= maxSelect) {
                    btn.classList.add('review-pref-shake');
                    setTimeout(function () { btn.classList.remove('review-pref-shake'); }, 400);
                    return;
                }

                preferences[key].push(value);
                btn.classList.add('review-pref-option--selected');
            });
        });

        // Owns-set choice cards on the first step (when shown)
        rootEl.querySelectorAll('[data-role="owns-choice"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const value = btn.dataset.value === 'yes' ? '1' : '0';
                if (ownsSetInput) {
                    ownsSetInput.value = value;
                }
                rootEl.querySelectorAll('[data-role="owns-choice"]').forEach(function (other) {
                    other.classList.remove('review-owns-card--selected');
                });
                btn.classList.add('review-owns-card--selected');
                // Auto-advance to next step
                setTimeout(goNext, 160);
            });
        });

        function showStep(idx) {
            currentIndex = idx;
            rootEl.querySelectorAll('.review-step').forEach(function (step) {
                step.hidden = true;
            });
            const key = stepsOrder[idx];
            const stepEl = rootEl.querySelector('.review-step[data-step="' + key + '"]');
            if (stepEl) {
                stepEl.hidden = false;
            }
            if (stepTitleNode) {
                stepTitleNode.textContent = stepTitles[key] || '';
            }
            if (stepIndicator) {
                const template = i18n.stepOf || 'Step {current} of {total}';
                stepIndicator.textContent = template
                    .replace('{current}', String(idx + 1))
                    .replace('{total}', String(totalSteps));
            }
            if (progressBar) {
                const pct = Math.round(((idx + 1) / totalSteps) * 100);
                progressBar.style.width = pct + '%';
            }
            if (backBtn) {
                backBtn.hidden = idx === 0;
            }
            const isLast = idx === stepsOrder.length - 1;
            if (nextBtn) {
                nextBtn.hidden = isLast;
            }
            if (submitBtn) {
                submitBtn.hidden = !isLast;
            }
            if (choiceSwitchBtn) {
                choiceSwitchBtn.hidden = idx !== 0;
            }
            if (key === 'summary') {
                renderSummary();
            }
            const wizardBody = rootEl.querySelector('.review-wizard-body');
            if (wizardBody) {
                wizardBody.scrollTop = 0;
            }
        }

        function collectDimensionScores() {
            const scores = {};
            rootEl.querySelectorAll('[data-role="score-slider"][data-dimension]').forEach(function (slider) {
                const dim = slider.dataset.dimension;
                scores[dim] = Number(slider.value);
            });
            return scores;
        }

        function renderSummary() {
            const scores = collectDimensionScores();
            const dimensionKeys = Object.keys(scores);
            if (dimensionKeys.length === 0) return;
            const avg = dimensionKeys.reduce(function (sum, k) { return sum + scores[k]; }, 0) / dimensionKeys.length;
            const rounded = Math.round(avg / 0.25) * 0.25;
            if (overallDisplay) {
                overallDisplay.textContent = rounded.toFixed(2);
            }
            updateStars(overallStars, rounded);
        }

        function goNext() {
            if (currentIndex < stepsOrder.length - 1) {
                showStep(currentIndex + 1);
            }
        }

        function goBack() {
            if (currentIndex > 0) {
                showStep(currentIndex - 1);
            }
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', goNext);
        }
        if (backBtn) {
            backBtn.addEventListener('click', goBack);
        }

        const originalSubmitLabel = submitBtn ? submitBtn.innerHTML : '';

        function restoreSubmitBtn() {
            if (!submitBtn) return;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalSubmitLabel;
        }

        function submit() {
            hideAlert(rootEl);
            const payload = {
                set_id: Number(rootEl.dataset.setId || 0),
                scores: collectDimensionScores(),
                answers: { ...answers },
                preferences: { ...preferences },
                title: titleEl ? titleEl.value : '',
                content: contentEl ? contentEl.value : '',
                owns_set: ownsSetInput ? ownsSetInput.value === '1' : false,
            };

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (i18n.saving || 'Saving...');
            }

            fetch(rootEl.dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.data && result.data.success) {
                        reloadPage(getRedirectStrategy(result.data));
                        return;
                    }
                    const message = (result.data && result.data.message) || i18n.errorGeneric || 'An error occurred. Please try again.';
                    showAlert(rootEl, message);
                    restoreSubmitBtn();
                })
                .catch(function () {
                    showAlert(rootEl, i18n.errorConnection || 'Connection error. Check your internet and try again.');
                    restoreSubmitBtn();
                });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', submit);
        }

        showStep(0);
    }

    // ─── Stats panel (radar + histogram + CTA) ───────────────────────────────

    function bindRadarCharts(scope) {
        if (typeof Chart === 'undefined') return;
        const canvases = (scope || document).querySelectorAll('canvas[data-role="radar-chart"]');
        canvases.forEach(function (canvas) {
            if (canvas.dataset.chartBound === '1') return;
            canvas.dataset.chartBound = '1';
            let data;
            try {
                data = JSON.parse(canvas.dataset.chart || '{}');
            } catch (_) {
                data = { labels: [], values: [] };
            }
            const labels = Array.isArray(data.labels) ? data.labels : [];
            const values = Array.isArray(data.values) ? data.values : [];
            if (labels.length === 0) return;

            new Chart(canvas.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Avg',
                        data: values,
                        fill: true,
                        backgroundColor: 'rgba(13, 110, 253, 0.18)',
                        borderColor: 'rgba(13, 110, 253, 0.9)',
                        pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.parsed.r.toFixed(1) + ' / 10';
                                },
                            },
                        },
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 10,
                            ticks: { stepSize: 2, backdropColor: 'transparent' },
                            grid: { color: 'rgba(0,0,0,0.08)' },
                            angleLines: { color: 'rgba(0,0,0,0.08)' },
                            pointLabels: {
                                font: { size: 12, weight: '600' },
                            },
                        },
                    },
                },
            });
        });
    }

    // ─── Boot ─────────────────────────────────────────────────────────────────

    function bootScopeBindings(scope) {
        scope.querySelectorAll('.review-simple-form').forEach(bindSimpleForm);
        scope.querySelectorAll('.review-wizard').forEach(bindDetailedWizard);
        bindRadarCharts(scope);
    }

    function init() {
        bootScopeBindings(document);

        const mainModal = document.getElementById('mainModal');
        if (mainModal) {
            mainModal.addEventListener('shown.bs.modal', function () {
                bootScopeBindings(mainModal);
            });
            // The modal content is replaced via $.ajax.success, after which loadfields() may run.
            // We observe to catch all replacements.
            const observer = new MutationObserver(function () {
                bootScopeBindings(mainModal);
            });
            observer.observe(mainModal, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
