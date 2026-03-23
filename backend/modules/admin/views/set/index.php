<?php

use common\enums\StatusEnum;
use common\models\Set;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View                           $this
 * @var backend\modules\admin\models\SetSearch $searchModel
 * @var yii\data\ActiveDataProvider            $dataProvider
 */

$this->title = 'Sets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="set-index">


    <div class="d-flex align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Create Set', ['create'], ['class' => 'btn btn-sm btn-success']) ?>
        </div>
    </div>


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'columns'      => [
                    [
                            'attribute' => 'number',
                            'options'   => ['style' => 'width: 100px;'],
                    ],
                    [
                            'attribute' => 'name',
                            'format'    => 'raw',
                            'value'     => static function (Set $model): string {
                                $label = $model->name ?: '-';

                                return Html::a(Html::encode($label), ['/admin/set/view', 'id' => $model->id], [
                                        'class' => 'text-decoration-none fw-semibold',
                                ]);
                            },
                    ],
                    [
                            'attribute' => 'theme_id',
                            'label'     => 'Theme',
                            'filter'    => Set::getAvailableThemesList(),
                            'value'     => static function (Set $model): string {
                                return $model->theme->name ?? '-';
                            },
                            'options'   => ['style' => 'width: 250px;'],
                    ],
                    [
                            'attribute' => 'status',
                            'filter'    => StatusEnum::options(),
                            'format'    => 'raw',
                            'value'     => static function (Set $model): string {
                                return Html::beginForm(['toggle-status', 'id' => $model->id], 'post', ['class' => 'mb-0 d-flex justify-content-center']) .
                                        Html::hiddenInput('returnUrl', Url::current()) .
                                        '<div class="form-check form-switch mb-0">' .
                                        Html::checkbox('status', $model->isActive(), [
                                                'class'    => 'form-check-input',
                                                'role'     => 'switch',
                                                'label'    => '',
                                                'onchange' => 'this.form.submit()',
                                        ]) .
                                        '</div>' .
                                        Html::endForm();
                            },
                            'options'   => ['style' => 'width: 150px;'],
                    ],
                    [
                            'class'      => ActionColumn::class,
                            'template'   => '{view} {update} {delete}',
                            'options'    => ['style' => 'width: 150px;'],
                            'buttons'    => [
                                    'view'   => static function (string $url): string {
                                        return Html::a('<i class="bi bi-eye"></i>', $url, [
                                                'class' => 'btn btn-sm btn-outline-secondary',
                                                'title' => 'View',
                                        ]);
                                    },
                                    'update' => static function (string $url): string {
                                        return Html::a('<i class="bi bi-pencil"></i>', $url, [
                                                'class' => 'btn btn-sm btn-outline-primary',
                                                'title' => 'Update',
                                        ]);
                                    },
                                    'delete' => static function (string $url): string {
                                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                                                'class'        => 'btn btn-sm btn-outline-danger',
                                                'title'        => 'Delete',
                                                'data-method'  => 'post',
                                                'data-confirm' => 'Are you sure you want to delete this item?',
                                        ]);
                                    },
                            ],
                            'urlCreator' => function ($action, Set $model, $key, $index, $column) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            },
                    ],
            ],
            'pager'        => [
                    'class'   => LinkPager::class,
                    'options' => ['class' => 'pagination justify-content-center mt-4'],
            ],
    ]) ?>


</div>
