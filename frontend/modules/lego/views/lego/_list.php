<?php

use common\models\Set;
use frontend\components\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ListView;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 * @var $grouped      bool
 */

$grouped = $grouped ?? false;

?>

<?php if (!$grouped): ?>
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions'  => ['class' => 'col-6 col-lg-3 col-md-4 lego-set mb-4'],
        'itemView'     => '_item',
        'options'      => ['class' => 'row'],
        'summary'      => false,
        'pager'        => [
            'class' => LinkPager::class,
        ],
    ]) ?>
<?php else: ?>
    <?php
    $dataProvider->prepare();
    $models = $dataProvider->getModels();

    $groups = [];
    foreach ($models as $model) {
        /** @var Set $model */
        if (empty($model->launch_date)) {
            continue;
        }
        $groups[date('Y-m', strtotime($model->launch_date))][] = $model;
    }
    ?>

    <?php foreach ($groups as $key => $items): ?>
        <div class="new-arrivals-month-section" data-key="<?= $key ?>">
            <div class="new-arrivals-month-header">
                <a href="<?= Html::encode(Url::to(['/lego', 'year' => date('Y', strtotime("$key-01")), 'month' => date('n', strtotime("$key-01"))])) ?>" class="new-arrivals-month-label">
                    <i class="bi bi-calendar3 me-2"></i>
                    <?= Html::encode(Yii::$app->formatter->asDate(strtotime("$key-01"), 'LLLL yyyy')) ?>
                </a>
            </div>
            <div class="row">
                <?php foreach ($items as $model): ?>
                    <div class="col-6 col-lg-3 col-md-4 lego-set mb-4">
                        <?= $this->render('_item', ['model' => $model]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($dataProvider->pagination): ?>
        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    <?php endif; ?>
<?php endif; ?>
