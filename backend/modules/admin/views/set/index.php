<?php

use common\enums\image\StatusEnum;
use common\models\Set;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var backend\modules\admin\models\SetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

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
                    ['class' => 'yii\grid\SerialColumn'],
                    'number',
                    'name',
                    [
                            'attribute' => 'theme_id',
                            'label'     => 'Theme',
                            'filter'    => Set::getAvailableThemesList(),
                            'value'     => static function (Set $model): string {
                                return $model->theme->name ?? '-';
                            },
                    ],
                    [
                            'attribute' => 'status',
                            'filter'    => array_reduce(
                                    StatusEnum::cases(),
                                    static function (array $carry, StatusEnum $status): array {
                                        $carry[$status->value] = $status->label();

                                        return $carry;
                                    },
                                    []
                            ),
                            'value'     => static function (Set $model): string {
                                return StatusEnum::tryFrom((int)$model->status)?->label() ?? '-';
                            },
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
