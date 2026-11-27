<?php

use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var array<int, array<string, mixed>> $runs
 * @var list<array{time: string, who: string, result: string, detail: string, sql: string}> $queries
 */

$this->title = T::tr('Admin tools activity');
$this->params['robots'] = 'noindex,nofollow';
$runBadge = ['done' => 'success', 'failed' => 'danger', 'running' => 'primary', 'lost' => 'secondary'];
$sqlBadge = ['OK' => 'success', 'REFUSED' => 'warning', 'ERROR' => 'danger'];
?>
<div class="container py-4">
    <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted small mb-4">Console commands (including deploys) and read-only SQL queries run through /management, newest first. Sources: console/runtime/console-runs and frontend/runtime/logs/db-query.log.</p>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#act-runs" type="button" role="tab">Commands (<?= count($runs) ?>)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#act-sql" type="button" role="tab">SQL queries (<?= count($queries) ?>)</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="act-runs" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Started</th><th>Command</th><th>Status</th><th>Took</th><th>By</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($runs as $run):
                        $took = isset($run['finished']) ? max(0, (int)$run['finished'] - (int)$run['started']) : null; ?>
                        <tr>
                            <td class="text-nowrap"><?= Html::encode(date('Y-m-d H:i:s', (int)$run['started'])) ?></td>
                            <td><code><?= Html::encode((string)$run['command']) ?></code></td>
                            <td><span class="badge text-bg-<?= $runBadge[$run['status']] ?? 'secondary' ?>"><?= Html::encode((string)$run['status']) ?><?= isset($run['exitCode']) && $run['exitCode'] !== 0 ? ' (' . (int)$run['exitCode'] . ')' : '' ?></span></td>
                            <td class="text-nowrap"><?= $took === null ? '–' : Html::encode($took . ' s') ?></td>
                            <td><?= Html::encode((string)($run['who'] ?? '')) ?></td>
                            <td><?= Html::a('log', Url::to(['/management/console-view', 'id' => $run['id']])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($runs === []): ?>
                        <tr><td colspan="6" class="text-muted">No commands yet (or console.enabled is off).</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="act-sql" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm align-top">
                    <thead><tr><th>Time</th><th>Result</th><th>By</th><th>Query</th></tr></thead>
                    <tbody>
                    <?php foreach ($queries as $q): ?>
                        <tr>
                            <td class="text-nowrap small"><?= Html::encode($q['time']) ?></td>
                            <td class="text-nowrap small">
                                <?php if ($q['result'] !== ''): ?><span class="badge text-bg-<?= $sqlBadge[$q['result']] ?? 'secondary' ?>"><?= Html::encode($q['result']) ?></span><?php endif; ?>
                                <div class="text-muted"><?= Html::encode($q['detail']) ?></div>
                            </td>
                            <td class="small"><?= Html::encode($q['who']) ?></td>
                            <td><pre class="mb-0 small" style="white-space: pre-wrap; max-width: 820px;"><?= Html::encode($q['sql']) ?></pre></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($queries === []): ?>
                        <tr><td colspan="4" class="text-muted">No queries logged yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
