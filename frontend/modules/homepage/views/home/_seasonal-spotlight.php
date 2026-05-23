<?php

use frontend\components\T;
use yii\web\View;

/**
 * @var View       $this
 * @var array|null $event Payload from HomepageContentService::getSeasonalSpotlight()
 */

if (!is_array($event)) {
    return;
}

echo $this->render('@frontend/views/_partials/_page-hero', [
        'eyebrow'         => T::tr('Seasonal'),
        'title'           => (string)$event['label'],
        'intro'           => (string)$event['intro'],
        'image'           => (string)$event['image'],
        'icon'            => (string)$event['icon'],
        'modifier'        => (string)$event['key'],
        'showBreadcrumbs' => false,
        'ctaUrl'          => (string)$event['url'],
        'ctaLabel'        => (string)$event['ctaLabel'],
]);
