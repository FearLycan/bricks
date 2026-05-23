<?php

namespace frontend\components;

use common\enums\StatusEnum;
use common\models\Theme;
use Yii;

/**
 * Source of truth for the public "Interests" landing page at /interests.
 *
 * Inspired by LEGO.com /pl-pl/categories/interests but limited to entries
 * that point at a populated slice of our catalog. Each tile maps to one of:
 *   - a curated filter route (e.g. /lego/magazines, /lego/tag/<slug>),
 *   - an existing Theme page (/lego/theme/<slug>) — image is pulled from the
 *     Theme row when available, otherwise the tile falls back to its gradient.
 *
 * Adding an entry: drop one row into the array. No DB schema change needed.
 */
final class LegoInterests
{
    /**
     * @return array<int, array{
     *     key:string, name:string, description:string,
     *     url:array, icon:string, modifier:string,
     *     image:?string, group:string
     * }>
     */
    public static function getTiles(): array
    {
        $themeImages = self::loadThemeImages([
            'star-wars', 'harry-potter', 'city', 'friends',
            'marvel-super-heroes', 'ninjago', 'technic',
        ]);

        return [
            // --- Audience ---
            [
                'key'         => 'adults-welcome',
                'group'       => T::tr('By age'),
                'name'        => T::tr('Adults Welcome'),
                'description' => T::tr('Big display sets and Modular Buildings for builders 18+.'),
                'url'         => ['/lego/tag/18-plus'],
                'icon'        => 'bi-person',
                'modifier'    => 'adults',
                'image'       => null,
            ],
            [
                'key'         => 'for-kids',
                'group'       => T::tr('By age'),
                'name'        => T::tr('For Kids (5–12)'),
                'description' => T::tr('Sets that fit kids in the primary-school years.'),
                'url'         => ['/lego', 'age_min' => 5, 'age_max' => 12],
                'icon'        => 'bi-emoji-smile',
                'modifier'    => 'kids',
                'image'       => null,
            ],

            // --- Deals & lifecycle ---
            [
                'key'         => 'on-sale',
                'group'       => T::tr('Deals'),
                'name'        => T::tr('On Sale'),
                'description' => T::tr('Sets currently priced below the official LEGO retail.'),
                'url'         => ['/lego/on-sale'],
                'icon'        => 'bi-tags',
                'modifier'    => 'sale',
                'image'       => null,
            ],
            [
                'key'         => 'exclusive',
                'group'       => T::tr('Deals'),
                'name'        => T::tr('LEGO Exclusive'),
                'description' => T::tr('Sets you can only buy from LEGO.com or a LEGO brand store.'),
                'url'         => ['/lego/exclusive'],
                'icon'        => 'bi-gem',
                'modifier'    => 'exclusive',
                'image'       => null,
            ],
            [
                'key'         => 'retiring-soon',
                'group'       => T::tr('Deals'),
                'name'        => T::tr('Retiring Soon'),
                'description' => T::tr('Sets approaching retirement, when prices usually start to climb.'),
                'url'         => ['/lego/retiring-soon'],
                'icon'        => 'bi-hourglass-split',
                'modifier'    => 'retiring',
                'image'       => null,
            ],

            // --- Format ---
            [
                'key'         => 'magazines',
                'group'       => T::tr('Set type'),
                'name'        => T::tr('Magazine Sets'),
                'description' => T::tr('Mini-builds that come bundled with LEGO magazines.'),
                'url'         => ['/lego/magazines'],
                'icon'        => 'bi-newspaper',
                'modifier'    => 'magazines',
                'image'       => null,
            ],
            [
                'key'         => 'polybags',
                'group'       => T::tr('Set type'),
                'name'        => T::tr('Polybags'),
                'description' => T::tr('Cheap sealed-bag sets, handy as add-ons or for minifigure swaps.'),
                'url'         => ['/lego/tag/polybag'],
                'icon'        => 'bi-bag',
                'modifier'    => 'polybags',
                'image'       => null,
            ],
            [
                'key'         => 'gwp',
                'group'       => T::tr('Set type'),
                'name'        => T::tr('Gift with Purchase'),
                'description' => T::tr('Bonus sets that come free with a qualifying LEGO.com order.'),
                'url'         => ['/lego/tag/gift-with-purchase'],
                'icon'        => 'bi-gift',
                'modifier'    => 'gwp',
                'image'       => null,
            ],
            [
                'key'         => 'seasonal',
                'group'       => T::tr('Set type'),
                'name'        => T::tr('Seasonal'),
                'description' => T::tr('Holiday builds: Christmas, Halloween, Easter, Valentine.'),
                'url'         => ['/lego/tag/seasonal'],
                'icon'        => 'bi-snow',
                'modifier'    => 'seasonal',
                'image'       => null,
            ],

            // --- Big franchises (theme-backed) ---
            [
                'key'         => 'star-wars',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('Star Wars'),
                'description' => T::tr('Ships, characters and dioramas from across the saga.'),
                'url'         => ['/lego/theme/star-wars'],
                'icon'        => 'bi-stars',
                'modifier'    => 'starwars',
                'image'       => $themeImages['star-wars'] ?? null,
            ],
            [
                'key'         => 'harry-potter',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('Harry Potter'),
                'description' => T::tr('Hogwarts, Diagon Alley, Quidditch and the recent book-nook models.'),
                'url'         => ['/lego/theme/harry-potter'],
                'icon'        => 'bi-magic',
                'modifier'    => 'potter',
                'image'       => $themeImages['harry-potter'] ?? null,
            ],
            [
                'key'         => 'marvel',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('Marvel Super Heroes'),
                'description' => T::tr('Avengers, Spider-Man, X-Men and the rest of the Marvel roster.'),
                'url'         => ['/lego/theme/marvel-super-heroes'],
                'icon'        => 'bi-shield-fill',
                'modifier'    => 'marvel',
                'image'       => $themeImages['marvel-super-heroes'] ?? null,
            ],
            [
                'key'         => 'ninjago',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('Ninjago'),
                'description' => T::tr('Spinjitzu masters, dragons and the Ninjago world.'),
                'url'         => ['/lego/theme/ninjago'],
                'icon'        => 'bi-lightning-charge-fill',
                'modifier'    => 'ninjago',
                'image'       => $themeImages['ninjago'] ?? null,
            ],
            [
                'key'         => 'city',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('City'),
                'description' => T::tr('Police, fire, vehicles and everyday-life scenes from LEGO City.'),
                'url'         => ['/lego/theme/city'],
                'icon'        => 'bi-buildings',
                'modifier'    => 'city',
                'image'       => $themeImages['city'] ?? null,
            ],
            [
                'key'         => 'friends',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('Friends'),
                'description' => T::tr('Heartlake City sets — cafés, pets, friendship stories.'),
                'url'         => ['/lego/theme/friends'],
                'icon'        => 'bi-heart',
                'modifier'    => 'friends',
                'image'       => $themeImages['friends'] ?? null,
            ],
            [
                'key'         => 'technic',
                'group'       => T::tr('Themes'),
                'name'        => T::tr('Technic'),
                'description' => T::tr('Working gearboxes, motors and mechanical models.'),
                'url'         => ['/lego/theme/technic'],
                'icon'        => 'bi-gear-fill',
                'modifier'    => 'technic',
                'image'       => $themeImages['technic'] ?? null,
            ],
        ];
    }

    /**
     * Pull the best available image URL for each theme slug. Hero takes
     * precedence; the tile image is the fallback. Tiles whose theme has
     * neither stay on their gradient background.
     *
     * @param string[] $slugs
     * @return array<string, string|null>
     */
    private static function loadThemeImages(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $rows = Theme::find()
            ->select(['slug', 'image', 'hero_image'])
            ->where(['slug' => $slugs, 'status' => StatusEnum::ACTIVE->value])
            ->asArray()
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $hero  = trim((string)($row['hero_image'] ?? ''));
            $image = trim((string)($row['image'] ?? ''));
            $map[(string)$row['slug']] = $hero !== '' ? $hero : ($image !== '' ? $image : null);
        }

        return $map;
    }
}
