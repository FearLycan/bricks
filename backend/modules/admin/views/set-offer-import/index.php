<?php

use common\enums\SetOfferImportStatusEnum;
use common\models\SetOfferImport;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var backend\modules\admin\models\SetOfferImportSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = 'Offer Import Links';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="set-offer-import-index">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['style' => 'width: 80px;'],
            ],
            [
                'attribute' => 'setName',
                'label' => 'Set',
                'format' => 'raw',
                'value' => static function (SetOfferImport $model): string {
                    if ($model->set === null) {
                        return '-';
                    }

                    $setLabel = trim(($model->set->number ?: '-') . ' ' . ($model->set->name ?: ''));

                    return Html::a(Html::encode($setLabel), ['/admin/set/view', 'id' => $model->set_id, '#' => 'offers'], [
                        'class' => 'text-decoration-none',
                    ]);
                },
            ],
            [
                'attribute' => 'input_url',
                'format' => 'raw',
                'value' => static function (SetOfferImport $model): string {
                    return Html::a(Html::encode($model->input_url), $model->input_url, [
                        'target' => '_blank',
                        'rel' => 'noopener noreferrer',
                        'class' => 'text-break text-decoration-none',
                    ]);
                },
            ],
            [
                'attribute' => 'status',
                'filter' => SetOfferImportStatusEnum::options(),
                'format' => 'raw',
                'value' => static function (SetOfferImport $model): string {
                    $statusClass = match ($model->status) {
                        SetOfferImportStatusEnum::DONE->value => 'text-bg-success',
                        SetOfferImportStatusEnum::FAILED->value => 'text-bg-danger',
                        SetOfferImportStatusEnum::PROCESSING->value => 'text-bg-primary',
                        default => 'text-bg-secondary',
                    };

                    return Html::tag('span', Html::encode($model->getStatusLabel()), [
                        'class' => 'badge rounded-pill ' . $statusClass,
                    ]);
                },
                'options' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'attempts',
                'options' => ['style' => 'width: 90px;'],
            ],
            [
                'attribute' => 'error_message',
                'value' => static fn(SetOfferImport $model): string => $model->error_message ?: '-',
            ],
            [
                'attribute' => 'updated_at',
                'value' => static fn(SetOfferImport $model): string => (string)($model->updated_at ?? $model->created_at),
                'options' => ['style' => 'width: 180px;'],
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{update} {delete}',
                'options' => ['style' => 'width: 90px;'],
                'buttons' => [
                    'update' => static function (string $url): string {
                        return Html::a('<i class="bi bi-pencil"></i>', $url, ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Update']);
                    },
                    'delete' => static function (string $url): string {
                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Delete',
                            'data-method' => 'post',
                            'data-confirm' => 'Are you sure you want to delete this import link?',
                        ]);
                    },
                ],
            ],
        ],
        'pager' => [
            'class' => LinkPager::class,
            'options' => ['class' => 'pagination justify-content-center mt-4'],
        ],
    ]) ?>
</div>
