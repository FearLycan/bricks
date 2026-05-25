<?php

use common\components\Html;
use common\models\Set;
use frontend\components\T;

/**
 * @var $model Set
 */

$daysToExit = (int)$model->getDaysUntilExit();
?>

<div class="lego-status-banner lego-status-banner--retiring" role="status" aria-live="polite">
    <div class="lego-status-banner__head">
        <span class="lego-status-banner__dot" aria-hidden="true"></span>
        <span class="lego-status-banner__label"><?= T::tr('Retiring soon') ?></span>
    </div>
    <div class="lego-status-banner__body">
        <div class="lego-status-banner__count">
            <span class="lego-status-banner__count-number"><?= Html::encode((string)$daysToExit) ?></span>
            <span class="lego-status-banner__count-label">
                <?= T::tr('{n, plural, =0{retires today} =1{day until retirement} other{days until retirement}}', ['n' => $daysToExit]) ?>
            </span>
        </div>
        <div class="lego-status-banner__meta">
            <i class="bi bi-calendar-x" aria-hidden="true"></i>
            <?= T::tr('Retires on {date}', ['date' => date('d.m.Y', strtotime($model->exit_date))]) ?>
        </div>
    </div>
</div>
