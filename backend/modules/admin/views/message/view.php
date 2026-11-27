<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var common\models\ContactMessage $m
 */

$this->title = $m->subject;
$this->params['breadcrumbs'][] = ['label' => 'Messages', 'url' => ['/admin/message/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-view" style="max-width: 860px;">
    <p><?= Html::a('← All messages', Url::to(['/admin/message/index'])) ?></p>
    <h1 class="h4 mb-1"><?= Html::encode($m->subject) ?></h1>
    <p class="text-muted small">
        From <b><?= Html::encode($m->name) ?></b> &lt;<?= Html::a(Html::encode($m->email), 'mailto:' . $m->email . '?subject=' . rawurlencode('Re: ' . $m->subject)) ?>&gt;
        · <?= Html::encode(date('Y-m-d H:i', (int)$m->created_at)) ?> · language <?= Html::encode((string)$m->lang) ?>
    </p>
    <div class="border rounded p-3 bg-body-tertiary" style="white-space: pre-wrap;"><?= Html::encode($m->body) ?></div>
    <div class="d-flex gap-2 mt-3">
        <?= Html::a('Reply by email', 'mailto:' . $m->email . '?subject=' . rawurlencode('Re: ' . $m->subject), ['class' => 'btn btn-dark btn-sm']) ?>
        <?= Html::a('Mark unread', ['/admin/message/unread', 'id' => $m->id], ['class' => 'btn btn-outline-secondary btn-sm', 'data-method' => 'post']) ?>
        <?= Html::a('Delete', ['/admin/message/delete', 'id' => $m->id], ['class' => 'btn btn-outline-danger btn-sm', 'data-method' => 'post', 'data-confirm' => 'Delete this message?']) ?>
    </div>
</div>
