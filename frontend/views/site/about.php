<?php

/** @var yii\web\View $this */

use frontend\components\T;
use yii\helpers\Html;

$this->title = T::tr('About');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::encode(T::tr('This is the About page. You may modify the following file to customize its content:')) ?></p>

    <code><?= __FILE__ ?></code>
</div>
