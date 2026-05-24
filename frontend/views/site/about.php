<?php

/** @var yii\web\View $this */

use frontend\components\SeoHelper;
use frontend\components\T;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = T::tr('About BrickAtlas — LEGO® Price Tracker for AFOLs');
$this->params['metaDescription'] = T::tr('BrickAtlas is a free LEGO price tracker. We pull catalog data and match it against current retailer offers so you can spot real deals on sets.');
$this->params['canonicalUrl'] = SeoHelper::buildAbsoluteUrl(['/site/about']);
$this->params['breadcrumbs'][] = T::tr('About');
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">
        <?= Html::encode(T::tr('BrickAtlas is a price tracker and discovery tool for LEGO® fans.')) ?>
    </p>

    <p>
        <?= T::tr(
            'We pull set data from the LEGO catalog and match it against current store offers so you can spot real deals and track sets you care about. {browse} or jump straight to the {glossary}.',
            [
                'browse'   => Html::a(Html::encode(T::tr('Browse the catalog')), Url::to(['/lego'])),
                'glossary' => Html::a(Html::encode(T::tr('LEGO glossary')), Url::to(['/glossary'])),
            ]
        ) ?>
    </p>

    <p class="text-body-secondary small">
        <?= Html::encode(T::tr('Not affiliated with The LEGO Group. LEGO® is a trademark of the LEGO Group.')) ?>
    </p>
</div>
