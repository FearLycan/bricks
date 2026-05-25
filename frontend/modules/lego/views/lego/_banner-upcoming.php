<?php

use common\components\Html;
use common\models\Set;
use frontend\components\T;

/**
 * @var $model Set
 */

$daysToLaunch = (int)$model->getDaysUntilLaunch();
?>

<div class="lego-status-banner lego-status-banner--upcoming" role="status" aria-live="polite">
    <div class="lego-status-banner__head">
        <span class="lego-status-banner__dot" aria-hidden="true"></span>
        <span class="lego-status-banner__label"><?= T::tr('Not out yet') ?></span>
    </div>
    <div class="lego-status-banner__body">
        <div class="lego-status-banner__count">
            <span class="lego-status-banner__count-number"><?= Html::encode((string)$daysToLaunch) ?></span>
            <span class="lego-status-banner__count-label">
                <?= T::tr('{n, plural, =0{out today} =1{day until launch} other{days until launch}}', ['n' => $daysToLaunch]) ?>
            </span>
        </div>
        <div class="lego-status-banner__meta">
            <i class="bi bi-calendar-event" aria-hidden="true"></i>
            <?= T::tr('Launches on {date}', ['date' => date('d.m.Y', strtotime($model->launch_date))]) ?>
        </div>
    </div>
</div>
