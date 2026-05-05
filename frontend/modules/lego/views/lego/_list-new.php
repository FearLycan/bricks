<?php

use common\models\Set;
use frontend\components\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $this         View
 * @var $dataProvider ActiveDataProvider
 */

$dataProvider->prepare();
$models = $dataProvider->getModels();

$groups = [];
foreach ($models as $model) {
    /** @var Set $model */
    $month = date('F Y', strtotime($model->created_at));
    if ($model->release_date) {
        $month = date('F Y', strtotime($model->release_date));
    }
    $groups[$month][] = $model;
}

?>

<?php foreach ($groups as $month => $items): ?>
    <div class="new-arrivals-month-section">
        <div class="new-arrivals-month-header">
            <span class="new-arrivals-month-label">
                <i class="bi bi-calendar3 me-2"></i><?= Html::encode($month) ?>
            </span>
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
