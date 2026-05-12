(function () {
    'use strict';

    var searchWrap   = document.getElementById('bricksSearchWrap');
    var searchBox    = document.getElementById('bricksSearchBox');
    var searchToggle = document.getElementById('bricksSearchToggle');
    var searchInput  = document.getElementById('bricksSearchInput');
    var searchDrop   = document.getElementById('bricksSearchDropdown');
    var searchIcon   = document.getElementById('bricksSearchIcon');

    if (!searchBox) return;

    var debounceTimer   = null;
    var lastFetchedTerm = '';
    var isOpen          = false;

    function openSearch() {
        isOpen = true;
        searchBox.classList.add('is-open');
        searchIcon.className = 'bi bi-x-lg';
        setTimeout(function () { searchInput.focus(); }, 30);
    }

    function closeSearch() {
        isOpen = false;
        searchBox.classList.remove('is-open');
        searchIcon.className = 'bi bi-search';
        searchInput.value = '';
        hideDropdown();
        lastFetchedTerm = '';
    }

    function hideDropdown() {
        searchDrop.classList.remove('is-visible');
        searchDrop.innerHTML = '';
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderDropdown(data, term) {
        var sets   = data.sets   || [];
        var themes = data.themes || [];

        if (!sets.length && !themes.length) {
            searchDrop.innerHTML = '<div class="bricks-search-empty">No results found</div>';
            searchDrop.classList.add('is-visible');
            return;
        }

        var html = '';

        if (sets.length) {
            html += '<div class="bricks-search-group-label">Sets</div>';
            sets.forEach(function (item) {
                html += '<a class="bricks-search-item" href="' + esc(item.url) + '">'
                    + '<img class="bricks-search-item-img" src="' + esc(item.img) + '" alt="" loading="lazy">'
                    + '<div class="bricks-search-item-info">'
                    + '<span class="bricks-search-item-name">' + esc(item.name) + '</span>'
                    + '<span class="bricks-search-item-number">' + esc(item.number) + '</span>'
                    + '</div></a>';
            });
        }

        var total = data.setsTotal || 0;
        if (total > 0) {
            html += '<div class="bricks-search-footer">'
                + '<a class="bricks-search-footer-link" href="/lego?name=' + encodeURIComponent(term) + '">'
                + 'See all ' + total + ' matching sets'
                + '<i class="bi bi-arrow-right ms-1"></i>'
                + '</a></div>';
        }

        if (themes.length) {
            html += '<div class="bricks-search-divider"></div>';
            html += '<div class="bricks-search-group-label">Themes</div>';
            themes.forEach(function (item) {
                var label = item.parent
                    ? '<span class="bricks-search-theme-breadcrumb">' + esc(item.parent) + ' <i class="bi bi-chevron-right"></i> </span>' + esc(item.name)
                    : esc(item.name);
                html += '<a class="bricks-search-item" href="' + esc(item.url) + '">'
                    + '<div class="bricks-search-theme-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>'
                    + '<span class="bricks-search-item-name">' + label + '</span>'
                    + '</a>';
            });
        }

        searchDrop.innerHTML = html;
        applyDropdownPosition();
        searchDrop.classList.add('is-visible');
    }

    function applyDropdownPosition() {
        if (window.innerWidth >= 768) {
            searchDrop.style.cssText = '';
            return;
        }
        var rect = searchWrap.getBoundingClientRect();
        searchDrop.style.position = 'fixed';
        searchDrop.style.top      = (rect.bottom + 6) + 'px';
        searchDrop.style.left     = '0.75rem';
        searchDrop.style.right    = '0.75rem';
        searchDrop.style.width    = 'auto';
        searchDrop.style.minWidth = '0';
    }

    function fetchResults(term) {
        if (term === lastFetchedTerm) return;
        lastFetchedTerm = term;

        if (term.length < 2) {
            hideDropdown();
            return;
        }

        fetch('/autocomplete/search?term=' + encodeURIComponent(term), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (searchInput.value.trim() === term) {
                    renderDropdown(data, term);
                }
            })
            .catch(function () {});
    }

    searchToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isOpen) {
            closeSearch();
        } else {
            openSearch();
        }
    });

    searchInput.addEventListener('input', function () {
        var term = this.value.trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchResults(term);
        }, 280);
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSearch();
        }
    });

    document.addEventListener('click', function (e) {
        if (isOpen && !searchWrap.contains(e.target)) {
            closeSearch();
        }
    });
}());
