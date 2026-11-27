<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = 'Messages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-index">
    <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted small mb-3">Everything sent through the contact form. Unread in bold.</p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-sm align-middle'],
        'layout'       => "{items}\n{pager}",
        'rowOptions'   => static fn($m) => $m->read_at === null ? ['class' => 'fw-bold'] : [],
        'columns'      => [
            ['attribute' => 'created_at', 'label' => 'Received', 'value' => static fn($m) => date('Y-m-d H:i', (int)$m->created_at), 'contentOptions' => ['class' => 'text-nowrap']],
            ['attribute' => 'name', 'value' => static fn($m) => $m->name . ' <' . $m->email . '>'],
            ['attribute' => 'subject', 'format' => 'raw', 'value' => static fn($m) => Html::a(Html::encode($m->subject), Url::to(['/admin/message/view', 'id' => $m->id]))],
            ['attribute' => 'lang', 'label' => 'Lang'],
        ],
    ]) ?>
</div>
