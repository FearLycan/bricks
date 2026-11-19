<?php

namespace frontend\components;

use common\components\SeasonalEventResolver;
use yii\helpers\Url;

/**
 * Builds the data structures rendered by the site navbar. Currently the only
 * consumer is the "Categories" dropdown in views/layouts/main.php, which
 * mixes evergreen catalog filters with an optional seasonal bonus item.
 *
 * Not cached: URLs depend on the active language and the cost of building a
 * handful of entries plus one seasonal-window switch is negligible.
 */
final class MenuService
{
    /**
     * @return array<int, array{label:string,url:string,icon:string,modifier:?string,dividerAfter?:bool}>
     */
    public function getCategoryItems(): array
    {
        $gifts = $this->item(T::tr('Gift ideas'), GiftGuide::entryRoute(), 'ph ph-gift', 'gifts');
        $gifts['dividerAfter'] = true;

        $items = [
            $gifts,
            $this->item(T::tr('Magazines'), ['/lego/magazines'], 'ph ph-newspaper'),
            $this->item(T::tr('Polybags'), ['/lego/tag/polybag'], 'ph ph-bag'),
            $this->item(T::tr('Gift with Purchase'), ['/lego/tag/gift-with-purchase'], 'ph ph-gift'),
            $this->item(T::tr('Seasonal'), ['/lego/tag/seasonal'], 'ph ph-snowflake'),
        ];

        $seasonal = $this->buildSeasonalItem();
        if ($seasonal !== null) {
            $items[] = $seasonal;
        }

        $items[] = $this->item(T::tr('Exclusive'), ['/lego/exclusive'], 'ph ph-diamond');
        $items[] = $this->item(T::tr('Retiring Soon'), ['/lego/retiring-soon'], 'ph ph-hourglass-medium');
        $items[] = $this->item(T::tr('For Adults (18+)'), ['/lego/for-adults'], 'ph ph-user');

        return $items;
    }

    /**
     * @return array{label:string,url:string,icon:string,modifier:?string}|null
     */
    private function buildSeasonalItem(): ?array
    {
        $event = SeasonalEventResolver::getActiveEvent();
        if ($event === null) {
            return null;
        }

        $label = match ($event['key']) {
            SeasonalEventResolver::KEY_CHRISTMAS => T::tr('Christmas'),
            SeasonalEventResolver::KEY_HALLOWEEN => T::tr('Halloween'),
            SeasonalEventResolver::KEY_EASTER    => T::tr('Easter'),
            SeasonalEventResolver::KEY_VALENTINE => T::tr("Valentine's Day"),
            default                              => T::tr('Seasonal'),
        };

        return [
            'label'    => $label,
            'url'      => Url::to(['/lego/tag/seasonal', 'name' => $event['name_filter']]),
            'icon'     => $event['icon'],
            'modifier' => $event['key'],
        ];
    }

    /**
     * @param array|string $route
     * @return array{label:string,url:string,icon:string,modifier:?string}
     */
    private function item(string $label, $route, string $icon, ?string $modifier = null): array
    {
        return [
            'label'    => $label,
            'url'      => Url::to($route),
            'icon'     => $icon,
            'modifier' => $modifier,
        ];
    }
}
