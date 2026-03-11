<?php

use common\models\User;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View                            $this
 * @var backend\modules\admin\models\UserSearch $searchModel
 * @var yii\data\ActiveDataProvider             $dataProvider
 */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Create User', ['create'], ['class' => 'btn btn-sm btn-success']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'username',
            'email:email',
            [
                'attribute' => 'status',
                'filter' => User::getStatusOptions(),
                'format' => 'raw',
                'value' => static function (User $model): string {
                    return Html::beginForm(['toggle-status', 'id' => $model->id], 'post', ['class' => 'mb-0 d-flex justify-content-center']) .
                        Html::hiddenInput('returnUrl', Url::current()) .
                        '<div class="form-check form-switch mb-0">' .
                        Html::checkbox('status', $model->isActive(), [
                            'class' => 'form-check-input',
                            'role' => 'switch',
                            'label' => '',
                            'onchange' => 'this.form.submit()',
                        ]) .
                        '</div>' .
                        Html::endForm();
                },
                'options' => ['style' => 'width: 120px;'],
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'options' => ['style' => 'width: 150px;'],
                'buttons' => [
                    'view' => static function (string $url): string {
                        return Html::a('<i class="bi bi-eye"></i>', $url, ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'View']);
                    },
                    'update' => static function (string $url): string {
                        return Html::a('<i class="bi bi-pencil"></i>', $url, ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Update']);
                    },
                    'delete' => static function (string $url): string {
                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Delete',
                            'data-method' => 'post',
                            'data-confirm' => 'Are you sure you want to delete this item?',
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
