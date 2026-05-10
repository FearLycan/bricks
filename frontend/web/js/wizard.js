(function () {
    'use strict';

    // ─── Step configuration ────────────────────────────────────────────────────

    const TOTAL_STEPS = 6;

    // ─── State ─────────────────────────────────────────────────────────────────

    let _skipReset = false;

    let state = {
        step: 1,
        answers: {
            recipient: null,
            profile: null,
            budget: null,
            interests: [],
            size: null,
            year: null,
        },
        loading: false,
    };

    function resetState() {
        state = {
            step: 1,
            answers: { recipient: null, profile: null, budget: null, interests: [], size: null, year: null },
            loading: false,
        };
    }

    // ─── Step rendering ────────────────────────────────────────────────────────

    function getStepConfig(step) {
        const cfg = window.WizardConfig;
        switch (step) {
            case 1:
                return {
                    key: 'recipient',
                    title: 'Who are you shopping for?',
                    type: 'radio',
                    required: true,
                    options: [
                        { value: 'self', label: 'For myself', emoji: '👤', description: 'Looking for something for myself' },
                        { value: 'gift', label: 'As a gift',  emoji: '🎁', description: 'Want to make someone happy' },
                    ],
                };
            case 2:
                return {
                    key: 'profile',
                    title: state.answers.recipient === 'gift' ? 'Who is the gift for?' : 'What is your profile?',
                    type: 'radio',
                    required: true,
                    options: state.answers.recipient === 'gift' ? cfg.giftProfiles : cfg.selfProfiles,
                };
            case 3:
                return {
                    key: 'budget',
                    title: 'What is your budget?',
                    type: 'radio',
                    required: true,
                    options: cfg.budgetOptions,
                };
            case 4:
                return {
                    key: 'interests',
                    title: state.answers.recipient === 'gift' ? 'What does this person like?' : 'What do you like?',
                    subtitle: 'Choose up to 3 options (optional)',
                    type: 'checkbox',
                    required: false,
                    maxSelect: 3,
                    options: (cfg.interestsByProfile[state.answers.profile] || []).map(function (i) {
                        return { value: i.key, label: i.label, emoji: i.emoji };
                    }),
                };
            case 5:
                return {
                    key: 'size',
                    title: 'How large should the set be?',
                    type: 'radio',
                    required: true,
                    options: cfg.sizeOptions,
                };
            case 6:
                return {
                    key: 'year',
                    title: 'When should it be released?',
                    type: 'radio',
                    required: true,
                    options: cfg.yearOptions,
                };
        }
        return null;
    }

    function renderStep() {
        const config = getStepConfig(state.step);
        if (!config) return;

        const body = document.getElementById('wizardBody');
        if (!body) return;

        body.innerHTML = buildStepHtml(config);
        updateProgress();
        updateButtons(config);
        attachOptionListeners(config);
    }

    function buildStepHtml(config) {
        console.log(config);

        const count = config.options.length;
        let gridMod = count === 2 ? ' wizard-options-grid--2' : (count >= 6 ? ' wizard-options-grid--sm' : '');

        let html = '<div class="wizard-step">';
        html += '<h5 class="wizard-step-title">' + escHtml(config.title) + '</h5>';
        if (config.subtitle) {
            html += '<p class="wizard-step-subtitle">' + escHtml(config.subtitle) + '</p>';
        } else {
            html += '<div class="wizard-step-subtitle-spacer"></div>';
        }

        if (config.type === 'radio') {
            html += '<div class="wizard-options-grid' + gridMod + '">';
            for (const opt of config.options) {
                html += buildOptionCard(opt, config.key, 'radio', state.answers[config.key] === opt.value);
            }
            html += '</div>';
        } else if (config.type === 'checkbox') {
            html += '<div class="wizard-options-grid' + gridMod + '">';
            for (const opt of config.options) {
                html += buildOptionCard(opt, config.key, 'checkbox', state.answers.interests.includes(opt.value));
            }
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function buildOptionCard(opt, groupKey, type, isSelected) {
        const selectedClass = isSelected ? ' wizard-option--selected' : '';
        const desc = opt.description ? '<span class="wizard-option-desc">' + escHtml(opt.description) + '</span>' : '';
        return (
            '<div class="wizard-option' + selectedClass + '" data-group="' + escHtml(groupKey) + '" data-value="' + escHtml(opt.value) + '" data-type="' + type + '">' +
            '<span class="wizard-option-check"><i class="bi bi-check-lg"></i></span>' +
            '<span class="wizard-option-emoji">' + (opt.emoji || '') + '</span>' +
            '<span class="wizard-option-label">' + escHtml(opt.label) + '</span>' +
            desc +
            '</div>'
        );
    }

    function attachOptionListeners(config) {
        const body = document.getElementById('wizardBody');
        if (!body) return;

        body.querySelectorAll('.wizard-option').forEach((card) => {
            card.addEventListener('click', function () {
                const value = this.dataset.value;
                const type  = this.dataset.type;
                const group = this.dataset.group;

                if (type === 'radio') {
                    state.answers[group] = value;
                    body.querySelectorAll('.wizard-option').forEach((c) => c.classList.remove('wizard-option--selected'));
                    this.classList.add('wizard-option--selected');

                    // Reset dependent answers when recipient or profile changes
                    if (group === 'recipient') {
                        state.answers.profile   = null;
                        state.answers.interests = [];
                    }
                    if (group === 'profile') {
                        state.answers.interests = [];
                    }

                } else if (type === 'checkbox') {
                    const maxSelect = config.maxSelect || 99;
                    const idx = state.answers.interests.indexOf(value);
                    if (idx === -1) {
                        if (state.answers.interests.length < maxSelect) {
                            state.answers.interests.push(value);
                            this.classList.add('wizard-option--selected');
                        } else {
                            shakeElement(this);
                        }
                    } else {
                        state.answers.interests.splice(idx, 1);
                        this.classList.remove('wizard-option--selected');
                    }
                }

                updateButtons(config);
            });
        });
    }

    // ─── Navigation ────────────────────────────────────────────────────────────

    function updateProgress() {
        const dots = document.getElementById('wizardDots');
        const ind  = document.getElementById('wizardStepIndicator');
        if (dots) {
            let html = '';
            for (let i = 1; i <= TOTAL_STEPS; i++) {
                const cls = i < state.step ? 'wizard-dot--done' : (i === state.step ? 'wizard-dot--active' : '');
                html += '<span class="wizard-dot ' + cls + '"></span>';
            }
            dots.innerHTML = html;
        }
        if (ind) ind.textContent = 'Step ' + state.step + ' of ' + TOTAL_STEPS;
    }

    function updateButtons(config) {
        const backBtn = document.getElementById('wizardBackBtn');
        const nextBtn = document.getElementById('wizardNextBtn');
        if (!backBtn || !nextBtn) return;

        backBtn.hidden = state.step === 1;

        const hasAnswer = isStepAnswered(config);
        nextBtn.disabled = config.required && !hasAnswer;

        if (state.step === TOTAL_STEPS) {
            nextBtn.innerHTML = '<i class="bi bi-search me-1"></i>Find Sets';
        } else {
            nextBtn.innerHTML = 'Next<i class="bi bi-arrow-right ms-1"></i>';
        }
    }

    function isStepAnswered(config) {
        if (!config.required) return true;
        if (config.type === 'radio') return !!state.answers[config.key];
        return true;
    }

    function goNext() {
        const config = getStepConfig(state.step);
        if (!config) return;
        if (config.required && !isStepAnswered(config)) { shakeElement(document.getElementById('wizardNextBtn')); return; }

        if (state.step === TOTAL_STEPS) {
            submitWizard();
            return;
        }

        state.step++;
        renderStep();
        scrollBodyToTop();
    }

    function goBack() {
        if (state.step <= 1) return;
        state.step--;
        renderStep();
        scrollBodyToTop();
    }

    function scrollBodyToTop() {
        const body = document.getElementById('wizardBody');
        if (body) body.scrollTop = 0;
    }

    // ─── API submit ────────────────────────────────────────────────────────────

    function submitWizard() {
        if (state.loading) return;
        state.loading = true;

        const nextBtn = document.getElementById('wizardNextBtn');
        if (nextBtn) {
            nextBtn.disabled = true;
            nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Searching...';
        }

        const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        fetch('/wizard/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token':  csrfToken,
            },
            body: JSON.stringify({ answers: state.answers }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.hash) {
                window.location.href = '/lego?wizard=' + encodeURIComponent(data.hash);
            } else {
                showError(data.error || 'An error occurred. Please try again.');
                resetSubmitButton();
            }
        })
        .catch(function () {
            showError('Connection error. Check your internet and try again.');
            resetSubmitButton();
        });
    }

    function resetSubmitButton() {
        state.loading = false;
        const nextBtn = document.getElementById('wizardNextBtn');
        if (nextBtn) {
            nextBtn.disabled = false;
            nextBtn.innerHTML = '<i class="bi bi-search me-1"></i>Find Sets';
        }
    }

    function showError(msg) {
        const body = document.getElementById('wizardBody');
        if (!body) return;
        const existing = body.querySelector('.wizard-error-alert');
        if (existing) existing.remove();
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger wizard-error-alert mt-3 mb-0';
        alert.textContent = msg;
        body.appendChild(alert);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    function escHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function shakeElement(el) {
        if (!el) return;
        el.classList.remove('wizard-shake');
        void el.offsetWidth;
        el.classList.add('wizard-shake');
        el.addEventListener('animationend', function () { el.classList.remove('wizard-shake'); }, { once: true });
    }

    // ─── Initialization ────────────────────────────────────────────────────────

    function init() {
        const modalEl = document.getElementById('wizardModal');
        if (!modalEl) return;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-wizard-answers]');
            if (!btn) return;
            try {
                resetState();
                const answers = JSON.parse(btn.dataset.wizardAnswers);
                state.answers = Object.assign(state.answers, answers);
                _skipReset = true;
            } catch (_) {
                _skipReset = false;
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        modalEl.addEventListener('show.bs.modal', function () {
            if (_skipReset) {
                _skipReset = false;
            } else {
                resetState();
            }
            renderStep();
        });

        document.getElementById('wizardNextBtn')?.addEventListener('click', goNext);
        document.getElementById('wizardBackBtn')?.addEventListener('click', goBack);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
