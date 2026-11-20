<?php

use frontend\components\GiftGuide;
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
        // The homepage already has its own (visually hidden) h1.
        'headingTag'      => 'h2',
        'ctaUrl'          => (string)$event['url'],
        'ctaLabel'        => (string)$event['ctaLabel'],
        // Before Christmas the banner also carries the gift guide picker
        // (the separate gift guide strip is then skipped in index.php).
        'aside'           => ($event['key'] ?? '') === 'christmas' && GiftGuide::isGiftSeason()
            ? $this->render('_gift-picker')
            : '',
]);
