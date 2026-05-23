<?php

use common\models\Log;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var Log          $model
 */

$this->title = 'Log #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

$levelClass = match ($model->level) {
    Log::LEVEL_ERROR   => 'text-bg-danger',
    Log::LEVEL_WARNING => 'text-bg-warning text-dark',
    Log::LEVEL_INFO    => 'text-bg-info text-dark',
    default            => 'text-bg-secondary',
};
?>
<div class="log-view">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <div>
            <h1 class="mb-1">Log entry #<?= Html::encode((string)$model->id) ?></h1>
            <div class="text-body-secondary">
                <span class="badge rounded-pill <?= $levelClass ?>"><?= Html::encode($model->getLevelLabel()) ?></span>
                <span class="ms-2"><?= Html::encode($model->getSourceLabel()) ?></span>
                <span class="ms-2"><?= Html::encode((string)$model->created_at) ?></span>
                <?php if ($model->isResolved()): ?>
                    <span class="badge rounded-pill text-bg-success ms-2">Resolved at <?= Html::encode((string)$model->resolved_at) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> Back', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?php if (!$model->isResolved()): ?>
                <?= Html::beginForm(['/admin/log/mark-resolved', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                <?= Html::hiddenInput('returnUrl', Url::to(['view', 'id' => $model->id])) ?>
                <?= Html::submitButton('<i class="bi bi-check2"></i> Mark resolved', ['class' => 'btn btn-sm btn-success']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?= Html::beginForm(['/admin/log/delete', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('<i class="bi bi-trash"></i> Delete', [
                'class'        => 'btn btn-sm btn-outline-danger',
                'data-confirm' => 'Delete this log entry?',
            ]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-2">Category</dt>
                <dd class="col-sm-10"><code><?= Html::encode($model->category ?: '-') ?></code></dd>

                <dt class="col-sm-2">Request</dt>
                <dd class="col-sm-10"><code><?= Html::encode($model->prefix ?: '-') ?></code></dd>

                <dt class="col-sm-2">Occurred at</dt>
                <dd class="col-sm-10"><?= Html::encode((string)$model->created_at) ?></dd>

                <?php if ($model->isResolved()): ?>
                    <dt class="col-sm-2">Resolved at</dt>
                    <dd class="col-sm-10"><?= Html::encode((string)$model->resolved_at) ?></dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent">
            <strong>Message</strong>
        </div>
        <div class="card-body">
            <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;"><?= Html::encode($model->message) ?></pre>
        </div>
    </div>

    <?php if (!empty($model->trace)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent">
                <strong>Stack trace</strong>
            </div>
            <div class="card-body">
                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word; max-height: 60vh; overflow: auto;"><?= Html::encode($model->trace) ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($model->context)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <strong>Extra context</strong>
            </div>
            <div class="card-body">
                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;"><?= Html::encode($model->context) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>
