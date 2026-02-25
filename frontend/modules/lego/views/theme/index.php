<?php

use common\components\Html;
use common\models\Theme;
use frontend\components\Helper;
use frontend\models\searches\SetSearch;
use yii\data\ActiveDataProvider;
use yii\web\View;

/**
 * @var $this         View
 * @var $theme        Theme
 * @var $subTheme     Theme
 * @var $dataProvider ActiveDataProvider
 * @var $searchModel  SetSearch
 */

$this->title = Html::encode($theme->name);

$this->params['breadcrumbs'][] = ['label' => Helper::getLegoName(), 'url' => ['/lego']];

if ($subTheme) {
    $this->title = Html::encode($subTheme->name);
    $this->params['breadcrumbs'][] = ['label' => $theme->name, 'url' => ['/lego/theme/' . $theme->slug]];
    $this->params['breadcrumbs'][] = $subTheme->name;
} else {
    $this->title = Html::encode($theme->name);
    $this->params['breadcrumbs'][] = $this->title;
}

?>


<div class="col-lg-12 mt-4">
    <h1 class="page-title">
        <?= $this->title ?>
    </h1>
</div>

<?= $this->render('/lego/_search', ['model' => $searchModel]) ?>

<?= $this->render('/lego/_list', ['dataProvider' => $dataProvider]) ?>
