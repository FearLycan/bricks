<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var array<int, array<string, mixed>> $runs
 */

$this->title = 'Console runs';
$this->params['robots'] = 'noindex,nofollow';
$badge = ['done' => 'success', 'failed' => 'danger', 'running' => 'primary', 'lost' => 'secondary'];
?>
<div class="container py-4">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted small">Allowlisted <code>php yii</code> commands started from /management/console-run (newest first). Logs: console/runtime/console-runs.</p>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Started</th><th>Command</th><th>Status</th><th>Took</th><th>By</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($runs as $run):
                $took = isset($run['finished']) ? max(0, (int)$run['finished'] - (int)$run['started']) : null; ?>
                <tr>
                    <td class="text-nowrap"><?= Html::encode(date('Y-m-d H:i:s', (int)$run['started'])) ?></td>
                    <td><code><?= Html::encode((string)$run['command']) ?></code></td>
                    <td><span class="badge text-bg-<?= $badge[$run['status']] ?? 'secondary' ?>"><?= Html::encode($run['status']) ?><?= isset($run['exitCode']) && $run['exitCode'] !== 0 ? ' (' . (int)$run['exitCode'] . ')' : '' ?></span></td>
                    <td class="text-nowrap"><?= $took === null ? '–' : Html::encode($took . ' s') ?></td>
                    <td><?= Html::encode((string)($run['who'] ?? '')) ?></td>
                    <td><?= Html::a('log', Url::to(['/management/console-view', 'id' => $run['id']])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($runs === []): ?>
                <tr><td colspan="6" class="text-muted">No runs yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
