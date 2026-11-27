<?php

/** @var yii\web\View $this */
/** @var int $days */
/** @var int[] $ranges */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Traffic';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/js/vendor/chart-4.6.2.umd.min.js', ['position' => View::POS_END]);
$this->registerJs('window.trConfig = ' . json_encode([
    'dataUrl' => Url::to(['/admin/traffic/data']),
    'pageUrl' => Url::to(['/admin/traffic/index']),
    'days' => $days,
    'siteUrl' => rtrim((string)(Yii::$app->params['frontend.baseUrl'] ?? ''), '/') ?: 'https://brickatlas.shop',
], JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) . ';', View::POS_HEAD);

$this->registerCss(<<<'CSS'
#tr-root{--admin-accent:#1b1f3b;--admin-muted:#5b6077;--admin-ink:#1b1f3b;--admin-card:#fff;--admin-border:#e5e7eb;max-width:1240px;padding:0 0 3rem}
.tr-head{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:1.4rem}
.tr-head h1{margin:0}
.tr-head p{margin:.2rem 0 0;color:var(--admin-muted);font-size:.92rem}
.tr-tools{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
.tr-updated{font-size:.78rem;color:var(--admin-muted)}
.tr-range{display:inline-flex;border:1px solid var(--admin-border,#e5e7eb);border-radius:.6rem;overflow:hidden;background:var(--admin-card,#fff)}
.tr-range button{border:0;background:none;padding:.4rem .8rem;font-size:.85rem;font-weight:600;color:var(--admin-muted);cursor:pointer}
.tr-range button.is-active{background:var(--admin-accent);color:#fff}
.tr-refresh{border:1px solid var(--admin-border,#e5e7eb);background:var(--admin-card,#fff);border-radius:.6rem;padding:.35rem .6rem;color:var(--admin-muted);cursor:pointer;line-height:0}
.tr-refresh svg{width:16px;height:16px}
.tr-refresh.is-busy svg{animation:tr-spin .8s linear infinite}
@keyframes tr-spin{to{transform:rotate(360deg)}}
.tr-body{transition:opacity .15s ease}
.tr-body.is-loading{opacity:.45;pointer-events:none}
.tr-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.25rem}
.tr-kpi{background:var(--admin-card,#fff);border:1px solid var(--admin-border,#e5e7eb);border-radius:.9rem;padding:1rem 1.1rem}
.tr-kpi__label{font-size:.75rem;font-weight:600;color:var(--admin-muted);text-transform:uppercase;letter-spacing:.03em}
.tr-kpi__value{font-size:1.65rem;font-weight:800;color:var(--admin-ink);line-height:1.2;margin-top:.25rem;display:flex;align-items:baseline;gap:.5rem;flex-wrap:wrap}
.tr-kpi__note{font-size:.78rem;color:var(--admin-muted);margin-top:.2rem}
.tr-kpi__spark{height:34px;margin-top:.5rem}
.tr-delta{font-size:.72rem;font-weight:700;padding:.1rem .4rem;border-radius:.4rem;background:rgba(127,127,127,.12);color:var(--admin-muted)}
.tr-delta--up{background:rgba(22,163,74,.12);color:#15803d}
.tr-delta--down{background:rgba(220,38,38,.12);color:#b91c1c}
.tr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1rem}
.tr-card{background:var(--admin-card,#fff);border:1px solid var(--admin-border,#e5e7eb);border-radius:.9rem;padding:1rem 1.1rem;min-width:0}
.tr-card--wide{grid-column:1/-1}
.tr-card h2{font-size:1rem;font-weight:700;margin:0 0 .2rem;color:var(--admin-ink)}
.tr-hint{font-size:.8rem;color:var(--admin-muted);margin:0 0 .6rem}
.tr-empty{font-size:.85rem;color:var(--admin-muted);margin:.3rem 0 0}
.tr-table{width:100%;font-size:.88rem}
.tr-table th{font-size:.72rem;color:var(--admin-muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em;padding:.35rem .4rem;border-bottom:1px solid var(--admin-border,#e5e7eb)}
.tr-table td{padding:.4rem;border-bottom:1px solid rgba(127,127,127,.1);vertical-align:middle}
.tr-table tr:last-child td{border-bottom:0}
.tr-table .num{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
.tr-table .main{word-break:break-word}
.tr-sub{display:block;font-size:.78rem;color:var(--admin-muted)}
.tr-bar{display:block;height:4px;border-radius:2px;background:rgba(127,127,127,.12);margin-top:.25rem;max-width:220px}
.tr-bar span{display:block;height:100%;border-radius:2px;background:var(--admin-accent)}
.tr-chart-box{position:relative;height:190px;margin:.4rem 0 .9rem}
.tr-chart-box--tall{height:290px;margin-bottom:0}
.tr-section{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--admin-muted);margin:1.6rem 0 .7rem}
.tr-error{background:rgba(220,38,38,.08);color:#b91c1c;border-radius:.6rem;padding:.7rem 1rem;margin-bottom:1rem;font-size:.9rem}
CSS);

$this->registerJs(<<<'JS'
(function () {
    var cfg = window.trConfig, root = document.getElementById('tr-root');
    if (!cfg || !root) return;
    var charts = {}, timer = null, current = cfg.days;
    var css = getComputedStyle(root);
    var accent = (css.getPropertyValue('--admin-accent') || '#1b1f3b').trim() || '#1b1f3b';
    var muted = (css.getPropertyValue('--admin-muted') || '#6b7280').trim() || '#6b7280';
    var cardBg = (css.getPropertyValue('--admin-card') || '#fff').trim() || '#fff';
    var gridColor = 'rgba(127,127,127,.15)';
    var palette = ['#f5b800', '#2563eb', '#16a34a', '#9333ea', '#f59e0b', '#0891b2', '#db2777', '#64748b', '#84cc16', '#475569'];

    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]; }); };
    var num = function (n, d) { return Number(n || 0).toLocaleString('en-US', {minimumFractionDigits: d || 0, maximumFractionDigits: d || 0}); };
    var alpha = function (hex, a) {
        var h = hex.replace('#', ''); if (h.length === 3) h = h.replace(/./g, '$&$&');
        var n = parseInt(h, 16); return 'rgba(' + (n >> 16 & 255) + ',' + (n >> 8 & 255) + ',' + (n & 255) + ',' + a + ')';
    };
    var delta = function (now, prev) {
        if (!prev) return now > 0 ? '<span class="tr-delta tr-delta--up">new</span>' : '';
        var p = (now - prev) / prev * 100;
        if (Math.abs(p) < 0.5) return '<span class="tr-delta">same</span>';
        return '<span class="tr-delta ' + (p > 0 ? 'tr-delta--up' : 'tr-delta--down') + '">' + (p > 0 ? '+' : '') + num(p) + '%</span>';
    };
    var ratio = function (a, b) { return b > 0 ? a / b : 0; };
    var bar = function (n, max) { return '<span class="tr-bar"><span style="width:' + (max > 0 ? Math.max(2, Math.round(n / max * 100)) : 0) + '%"></span></span>'; };
    var link = function (href, text) { if (href.charAt(0) === '/') { href = cfg.siteUrl + href; } return '<a href="' + esc(href) + '" target="_blank" rel="noopener">' + esc(text) + '</a>'; };
    var trim = function (s, n) { s = String(s || ''); return s.length > n ? s.slice(0, n - 1) + '…' : s; };
    var cap = function (s) { return s.charAt(0).toUpperCase() + s.slice(1); };
    var objRows = function (o) { return Object.keys(o || {}).map(function (k) { return [k, o[k]]; }); };

    var table = function (head, rows, empty) {
        if (!rows.length) return '<p class="tr-empty">' + esc(empty || 'No data yet.') + '</p>';
        var h = '<div class="table-responsive"><table class="tr-table"><thead><tr>';
        head.forEach(function (t, i) { h += '<th' + (i ? ' class="num"' : '') + '>' + esc(t) + '</th>'; });
        h += '</tr></thead><tbody>';
        rows.forEach(function (r) { h += '<tr>'; r.forEach(function (c, i) { h += '<td class="' + (i ? 'num' : 'main') + '">' + c + '</td>'; }); h += '</tr>'; });
        return h + '</tbody></table></div>';
    };
    var card = function (id, title, hint, body, chartId) {
        var el = document.getElementById(id); if (!el) return;
        el.innerHTML = '<h2>' + esc(title) + '</h2>' + (hint ? '<p class="tr-hint">' + esc(hint) + '</p>' : '')
            + (chartId ? '<div class="tr-chart-box"><canvas id="' + chartId + '"></canvas></div>' : '') + body;
    };
    var chart = function (id, config) {
        if (charts[id]) { charts[id].destroy(); delete charts[id]; }
        var c = document.getElementById(id);
        if (!c || !window.Chart) return;
        charts[id] = new Chart(c, config);
    };
    var hideBox = function (id) { var c = document.getElementById(id); if (c) c.parentNode.style.display = 'none'; };

    if (window.Chart) {
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        Chart.defaults.color = muted;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
    }

    var donut = function (id, labels, data) {
        if (!data.some(function (v) { return v > 0; })) return hideBox(id);
        chart(id, {type: 'doughnut', data: {labels: labels, datasets: [{data: data, backgroundColor: palette, borderColor: cardBg, borderWidth: 2, hoverOffset: 6}]},
            options: {cutout: '68%', plugins: {legend: {position: 'right'}}}});
    };
    var hbar = function (id, labels, data, label) {
        if (!data.length) return hideBox(id);
        chart(id, {type: 'bar', data: {labels: labels, datasets: [{label: label, data: data, backgroundColor: palette.map(function (c) { return alpha(c, .78); }), borderRadius: 4}]},
            options: {indexAxis: 'y', plugins: {legend: {display: false}}, scales: {x: {beginAtZero: true, grid: {color: gridColor}, border: {display: false}, ticks: {precision: 0}}, y: {grid: {display: false}}}}});
    };
    var spark = function (id, values, color) {
        chart(id, {type: 'line', data: {labels: values.map(function (_, i) { return i; }), datasets: [{data: values, borderColor: color, backgroundColor: alpha(color, .12), fill: true, borderWidth: 1.5, pointRadius: 0, tension: .35}]},
            options: {animation: false, plugins: {legend: {display: false}, tooltip: {enabled: false}}, scales: {x: {display: false}, y: {display: false, beginAtZero: true}}}});
    };

    var botNames = {google: 'Google', bing: 'Bing', pinterest: 'Pinterest', apple: 'Apple', yandex: 'Yandex', openai: 'OpenAI', claude: 'Claude', perplexity: 'Perplexity', meta: 'Meta', seo: 'SEO tools', other: 'Other'};

    function render(d) {
        var k = d.kpis, days = d.daily;
        var label = d.range.days === 1 ? 'Today compared with yesterday.' : 'Last ' + d.range.days + ' days compared with the ' + d.range.days + ' before.';
        document.getElementById('tr-sub').textContent = label + ' Counted on our server without cookies, so the consent banner does not hide anyone.';
        document.getElementById('tr-updated').textContent = 'Updated ' + new Date(d.generatedAt).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

        var col = function (f) { return days.map(f); };
        var kpis = [
            ['Visits', num(k.visits.now), delta(k.visits.now, k.visits.prev), 'Arrivals from outside the site', col(function (x) { return x.visits; }), '#f5b800'],
            ['Page views', num(k.views.now), delta(k.views.now, k.views.prev), num(ratio(k.views.now, k.visits.now), 1) + ' pages per visit', col(function (x) { return x.views; }), '#64748b'],
            ['Set page views', num(k.set_views.now), delta(k.set_views.now, k.set_views.prev), 'Views of individual set pages'],
            ['Searches', num(k.searches.now), delta(k.searches.now, k.searches.prev), 'Catalog name searches']
        ];
        var sparks = days.length > 1;
        document.getElementById('tr-kpis').innerHTML = kpis.map(function (c, i) {
            return '<div class="tr-kpi"><div class="tr-kpi__label">' + esc(c[0]) + '</div><div class="tr-kpi__value">' + esc(c[1]) + ' ' + c[2] + '</div>'
                + '<div class="tr-kpi__note">' + esc(c[3]) + '</div>' + (c[4] && sparks ? '<div class="tr-kpi__spark"><canvas id="tr-spark-' + i + '"></canvas></div>' : '') + '</div>';
        }).join('');
        kpis.forEach(function (c, i) { if (c[4] && sparks) spark('tr-spark-' + i, c[4], c[5]); });

        chart('tr-daily', {
            data: {labels: days.map(function (x) { return new Date(x.day + 'T00:00:00').toLocaleDateString('en-GB', {day: 'numeric', month: 'short'}); }),
                datasets: [
                    {type: 'bar', label: 'Visits', data: col(function (x) { return x.visits; }), backgroundColor: alpha('#f5b800', .65), hoverBackgroundColor: '#f5b800', borderRadius: 4, maxBarThickness: 48, order: 3},
                    {type: 'line', label: 'Page views', data: col(function (x) { return x.views; }), borderColor: '#94a3b8', backgroundColor: '#94a3b8', borderDash: [5, 4], borderWidth: 2, pointRadius: sparks ? 0 : 4, pointHoverRadius: 4, tension: .3, order: 2}
                ]},
            options: {interaction: {mode: 'index', intersect: false}, plugins: {legend: {position: 'top', align: 'start', reverse: true}},
                scales: {x: {grid: {display: false}, ticks: {autoSkip: true, maxTicksLimit: 12, maxRotation: 0}}, y: {beginAtZero: true, grid: {color: gridColor}, border: {display: false}, ticks: {precision: 0}}}}
        });

        var totalVisits = Math.max(1, k.visits.now);
        var maxS = Math.max.apply(null, [1].concat(d.sources.map(function (s) { return s.visits; })));
        card('tr-c-sources', 'Sources', 'Visits by where they came from.',
            table(['Source', 'Visits', 'Share', 'Change'], d.sources.map(function (s) {
                return [esc(s.label) + bar(s.visits, maxS), num(s.visits), num(s.visits / totalVisits * 100) + '%', delta(s.visits, s.prev)];
            })), 'tr-ch-sources');
        donut('tr-ch-sources', d.sources.map(function (s) { return s.label; }), d.sources.map(function (s) { return s.visits; }));

        card('tr-c-landing', 'Landing pages', 'The page a visit started on.',
            table(['Page', 'Type', 'Visits'], d.landing.map(function (r) { return [link(r.path, trim(r.path, 70)), esc(r.type), num(r.visits)]; })));
        card('tr-c-referrers', 'Referring sites', '',
            table(['Site', 'Source', 'Visits'], d.referrers.map(function (r) { return [esc(r.host), esc(r.source), num(r.visits)]; })));

        var devKeys = Object.keys(d.devices), devTotal = Math.max(1, devKeys.reduce(function (a, x) { return a + d.devices[x]; }, 0));
        card('tr-c-devices', 'Devices', '',
            table(['Device', 'Visits', 'Share'], devKeys.map(function (x) { return [esc(cap(x)) + bar(d.devices[x], devTotal), num(d.devices[x]), num(d.devices[x] / devTotal * 100) + '%']; })), 'tr-ch-devices');
        donut('tr-ch-devices', devKeys.map(cap), devKeys.map(function (x) { return d.devices[x]; }));

        card('tr-c-types', 'Page types', '',
            table(['Type', 'Visits started', 'Views'], d.pageTypes.map(function (r) { return [esc(r.label), num(r.visits), num(r.views)]; })));
        card('tr-c-searches', 'Searches', 'What people type into the search box.',
            table(['Query', 'Searches', 'Results'], d.searches.map(function (r) { return [link('/lego?name=' + encodeURIComponent(r.query), r.query), num(r.searches), num(r.results)]; })));
        card('tr-c-zero', 'Searches with no results', 'Sets or names people look for that the catalog does not find.',
            table(['Query', 'Searches'], d.zeroSearches.map(function (r) { return [esc(r.query), num(r.searches)]; }), 'None. Every search found something.'));

        var langNames = {en: 'English', pl: 'Polski', de: 'Deutsch', fr: 'Français', es: 'Español', it: 'Italiano', ja: '日本語'};
        var maxL = Math.max.apply(null, [1].concat(d.languages.map(function (r) { return r.visits; })));
        card('tr-c-langs', 'Languages', 'Page language of the visit.',
            table(['Language', 'Visits', 'Views', 'Change'], d.languages.map(function (r) { return [esc(langNames[r.lang] || r.lang) + bar(r.visits, maxL), num(r.visits), num(r.views), delta(r.visits, r.prev)]; })));

        var botRows = objRows(d.bots), maxB = Math.max.apply(null, [1].concat(botRows.map(function (r) { return r[1]; })));
        card('tr-c-bots', 'Crawler visits', 'Not counted as visits anywhere above.',
            table(['Crawler', 'Pages fetched'], botRows.map(function (r) { return [esc(botNames[r[0]] || r[0]) + bar(r[1], maxB), num(r[1])]; })), 'tr-ch-bots');
        hbar('tr-ch-bots', botRows.map(function (r) { return botNames[r[0]] || r[0]; }), botRows.map(function (r) { return r[1]; }), 'Pages fetched');
        card('tr-c-google', 'What Googlebot fetches', '', table(['Page type', 'Pages fetched'], objRows(d.google).map(function (r) { return [esc(r[0]), num(r[1])]; })));
        card('tr-c-404', 'Pages not found (404)', 'Broken links that people actually hit.',
            table(['Path', 'Hits'], d.notFound.map(function (r) { return [link(r.path, trim(r.path, 70)), num(r.hits)]; }), 'None.'));
    }

    function load(days, push) {
        current = days;
        document.querySelectorAll('.tr-range button').forEach(function (b) { b.classList.toggle('is-active', +b.dataset.days === days); });
        var body = document.getElementById('tr-body'), btn = document.getElementById('tr-refresh'), err = document.getElementById('tr-error');
        body.classList.add('is-loading'); btn.classList.add('is-busy');
        fetch(cfg.dataUrl + (cfg.dataUrl.indexOf('?') < 0 ? '?' : '&') + 'days=' + days, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'})
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) { err.hidden = true; render(d); })
            .catch(function (e) { err.hidden = false; err.textContent = 'Could not load the stats (' + e.message + '). Try again in a moment.'; })
            .then(function () { body.classList.remove('is-loading'); btn.classList.remove('is-busy'); });
        if (push && window.history && history.replaceState) {
            history.replaceState(null, '', cfg.pageUrl + (cfg.pageUrl.indexOf('?') < 0 ? '?' : '&') + 'days=' + days);
        }
        clearInterval(timer);
        timer = setInterval(function () { if (!document.hidden) load(current, false); }, 5 * 60 * 1000);
    }

    document.querySelectorAll('.tr-range button').forEach(function (b) {
        b.addEventListener('click', function () { load(+b.dataset.days, true); });
    });
    document.getElementById('tr-refresh').addEventListener('click', function () { load(current, false); });
    load(current, false);
})();
JS, View::POS_END);

$rangeLabel = static fn (int $r): string => $r === 1 ? 'Today' : ($r === 365 ? '1 year' : $r . ' days');
$refreshIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9 9 9 0 0 1-7.5-4"/><path d="M3 12a9 9 0 0 1 9-9 9 9 0 0 1 7.5 4"/><path d="M21 3v5h-5"/><path d="M3 21v-5h5"/></svg>';
?>
<div id="tr-root">
    <div class="tr-head">
        <div>
            <h1>Traffic</h1>
            <p id="tr-sub">Loading…</p>
        </div>
        <div class="tr-tools">
            <span class="tr-updated" id="tr-updated"></span>
            <button type="button" class="tr-refresh" id="tr-refresh" title="Refresh" aria-label="Refresh"><?= $refreshIcon ?></button>
            <div class="tr-range" role="group" aria-label="Period">
                <?php foreach ($ranges as $r): ?>
                    <button type="button" data-days="<?= $r ?>" class="<?= $r === $days ? 'is-active' : '' ?>"><?= Html::encode($rangeLabel($r)) ?></button>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <div class="tr-error" id="tr-error" hidden></div>

    <div class="tr-body is-loading" id="tr-body">
        <div class="tr-kpis" id="tr-kpis"></div>

        <section class="tr-card tr-card--wide">
            <h2>Day by day</h2>
            <div class="tr-chart-box tr-chart-box--tall"><canvas id="tr-daily" aria-label="Visits and page views per day"></canvas></div>
        </section>

        <div class="tr-section">Where visitors come from</div>
        <div class="tr-grid">
            <section class="tr-card" id="tr-c-sources"></section>
            <section class="tr-card" id="tr-c-landing"></section>
            <section class="tr-card" id="tr-c-referrers"></section>
            <section class="tr-card" id="tr-c-devices"></section>
            <section class="tr-card" id="tr-c-langs"></section>
        </div>

        <div class="tr-section">What they do here</div>
        <div class="tr-grid">
            <section class="tr-card" id="tr-c-types"></section>
            <section class="tr-card" id="tr-c-searches"></section>
            <section class="tr-card" id="tr-c-zero"></section>
        </div>

        <div class="tr-section">Crawlers and errors</div>
        <div class="tr-grid">
            <section class="tr-card" id="tr-c-bots"></section>
            <section class="tr-card" id="tr-c-google"></section>
            <section class="tr-card" id="tr-c-404"></section>
        </div>
    </div>
</div>
