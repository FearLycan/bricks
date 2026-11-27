<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var array<string, mixed> $run
 */

$this->title = 'Run ' . $run['id'];
$this->params['robots'] = 'noindex,nofollow';
?>
<div class="container py-4">
    <p><?= Html::a('← All runs', Url::to(['/management/console-history'])) ?></p>
    <h1 class="h4 mb-2"><code><?= Html::encode((string)$run['command']) ?></code></h1>
    <p class="text-muted small">
        Started <?= Html::encode(date('Y-m-d H:i:s', (int)$run['started'])) ?> by <?= Html::encode((string)($run['who'] ?? '')) ?>
        · status <b><?= Html::encode((string)$run['status']) ?></b>
        <?php if (isset($run['exitCode'])): ?> · exit <?= (int)$run['exitCode'] ?><?php endif; ?>
        · log <?= (int)$run['logBytes'] ?> B<?= $run['logBytes'] > 512 * 1024 ? ' (last 512 KB shown)' : '' ?>
    </p>
    <pre class="bg-dark text-light p-3 rounded small" style="max-height: 75vh; overflow: auto; white-space: pre-wrap;"><?= Html::encode((string)$run['log']) ?></pre>
</div>
