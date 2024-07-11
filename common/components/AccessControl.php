<?php

namespace common\components;

use yii\filters\AccessRule;

/**
 * Internal access control filter.
 */
class AccessControl extends \yii\filters\AccessControl
{
    /**
     * {@inheritdoc}
     */
    public $ruleConfig = [
        'class' => AccessRule::class,
    ];
}
