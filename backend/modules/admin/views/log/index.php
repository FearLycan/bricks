<?php

use backend\modules\admin\models\LogSearch;
use common\models\Log;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View                $this
 * @var LogSearch                   $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array<string,int>           $totals
 */

$this->title = 'Logs';
$this->params['breadcrumbs'][] = $this->title;
$currentReturnUrl = (string)Yii::$app->request->url;
?>
<div class="log-index">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary">Errors and warnings collected from console, backend and frontend.</div>
        </div>
        <div class="ms-auto d-flex gap-2 flex-wrap">
            <?= Html::beginForm(['/admin/log/mark-all'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('<i class="bi bi-check2-all"></i> Mark all resolved', [
                'class'        => 'btn btn-sm btn-outline-secondary',
                'data-confirm' => 'Mark every unresolved entry as resolved?',
            ]) ?>
            <?= Html::endForm() ?>
            <?= Html::beginForm(['/admin/log/clear-resolved'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('<i class="bi bi-trash3"></i> Delete resolved', [
                'class'        => 'btn btn-sm btn-outline-warning',
                'data-confirm' => 'Permanently delete all resolved entries?',
            ]) ?>
            <?= Html::endForm() ?>
            <?= Html::beginForm(['/admin/log/clear-all'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('<i class="bi bi-trash"></i> Delete all', [
                'class'        => 'btn btn-sm btn-outline-danger',
                'data-confirm' => 'Permanently delete EVERY log entry? This cannot be undone.',
            ]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-body-secondary mb-1">Total</div>
                    <div class="display-6 fw-semibold"><?= Html::encode((string)$totals['total']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-body-secondary mb-1">Unresolved errors</div>
                    <div class="display-6 fw-semibold text-danger"><?= Html::encode((string)$totals['errors']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-body-secondary mb-1">Unresolved warnings</div>
                    <div class="display-6 fw-semibold text-warning"><?= Html::encode((string)$totals['warnings']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-body-secondary mb-1">Resolved</div>
                    <div class="display-6 fw-semibold text-success"><?= Html::encode((string)$totals['resolved']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <?php $form = \yii\bootstrap5\ActiveForm::begin([
                'method' => 'get',
                'action' => ['index'],
                'options' => ['class' => 'row g-2 align-items-end'],
            ]); ?>
            <div class="col-md-2">
                <?= $form->field($searchModel, 'level')->dropDownList(
                    ['' => 'Any'] + Log::levelOptions(),
                    ['class' => 'form-select form-select-sm']
                )->label('Level') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($searchModel, 'source')->dropDownList(
                    ['' => 'Any'] + Log::sourceOptions(),
                    ['class' => 'form-select form-select-sm']
                )->label('Source') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($searchModel, 'resolved')->dropDownList(
                    ['' => 'Any', '0' => 'Unresolved', '1' => 'Resolved'],
                    ['class' => 'form-select form-select-sm']
                )->label('Status') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'message')->textInput([
                    'class'       => 'form-control form-control-sm',
                    'placeholder' => 'Search message…',
                ])->label('Message contains') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'category')->textInput([
                    'class'       => 'form-control form-control-sm',
                    'placeholder' => 'e.g. BricksetController::',
                ])->label('Category') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'createdFrom')->input('datetime-local', [
                    'class' => 'form-control form-control-sm',
                ])->label('From') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'createdTo')->input('datetime-local', [
                    'class' => 'form-control form-control-sm',
                ])->label('To') ?>
            </div>
            <div class="col-md-6 d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-search"></i> Filter', ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::a('Reset', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>
            <?php \yii\bootstrap5\ActiveForm::end(); ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table align-middle table-hover'],
        'columns'      => [
            [
                'attribute' => 'id',
                'options'   => ['style' => 'width: 70px;'],
            ],
            [
                'attribute' => 'level',
                'format'    => 'raw',
                'value'     => static function (Log $model): string {
                    $cssClass = match ($model->level) {
                        Log::LEVEL_ERROR   => 'text-bg-danger',
                        Log::LEVEL_WARNING => 'text-bg-warning text-dark',
                        Log::LEVEL_INFO    => 'text-bg-info text-dark',
                        default            => 'text-bg-secondary',
                    };
                    return Html::tag('span', Html::encode($model->getLevelLabel()), [
                        'class' => 'badge rounded-pill ' . $cssClass,
                    ]);
                },
                'options'   => ['style' => 'width: 110px;'],
            ],
            [
                'attribute' => 'source',
                'value'     => static fn(Log $model): string => $model->getSourceLabel(),
                'options'   => ['style' => 'width: 130px;'],
            ],
            [
                'attribute' => 'category',
                'value'     => static fn(Log $model): string => $model->category ?: '-',
                'options'   => ['style' => 'max-width: 260px;'],
                'contentOptions' => ['class' => 'text-truncate', 'style' => 'max-width: 260px;'],
            ],
            [
                'attribute' => 'message',
                'format'    => 'raw',
                'value'     => static function (Log $model): string {
                    return Html::a(
                        Html::encode($model->getShortMessage(160)),
                        ['view', 'id' => $model->id],
                        ['class' => 'text-decoration-none']
                    );
                },
            ],
            [
                'attribute' => 'created_at',
                'options'   => ['style' => 'width: 160px;'],
            ],
            [
                'label'   => 'Status',
                'format'  => 'raw',
                'value'   => static function (Log $model): string {
                    if ($model->isResolved()) {
                        return '<span class="badge rounded-pill text-bg-success">Resolved</span>';
                    }
                    return '<span class="badge rounded-pill text-bg-secondary">Open</span>';
                },
                'options' => ['style' => 'width: 110px;'],
            ],
            [
                'class'    => ActionColumn::class,
                'template' => '{view} {resolve} {delete}',
                'options'  => ['style' => 'width: 130px;'],
                'buttons'  => [
                    'view' => static function (string $url): string {
                        return Html::a('<i class="bi bi-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'View',
                        ]);
                    },
                    'resolve' => static function (string $url, Log $model) use ($currentReturnUrl): string {
                        if ($model->isResolved()) {
                            return '';
                        }
                        $resolveUrl = Url::to(['/admin/log/mark-resolved', 'id' => $model->id]);
                        $form = Html::beginForm($resolveUrl, 'post', ['class' => 'd-inline']);
                        $form .= Html::hiddenInput('returnUrl', $currentReturnUrl);
                        $form .= Html::submitButton('<i class="bi bi-check2"></i>', [
                            'class' => 'btn btn-sm btn-outline-success',
                            'title' => 'Mark resolved',
                        ]);
                        $form .= Html::endForm();
                        return $form;
                    },
                    'delete' => static function (string $url, Log $model): string {
                        $deleteUrl = Url::to(['/admin/log/delete', 'id' => $model->id]);
                        $form = Html::beginForm($deleteUrl, 'post', ['class' => 'd-inline']);
                        $form .= Html::submitButton('<i class="bi bi-trash"></i>', [
                            'class'        => 'btn btn-sm btn-outline-danger',
                            'title'        => 'Delete',
                            'data-confirm' => 'Delete this log entry?',
                        ]);
                        $form .= Html::endForm();
                        return $form;
                    },
                ],
            ],
        ],
        'pager' => [
            'class'   => LinkPager::class,
            'options' => ['class' => 'pagination justify-content-center mt-4'],
        ],
    ]) ?>
</div>
