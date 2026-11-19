<?php

namespace frontend\components;

/**
 * BrickAtlas' own accent colour per LEGO theme for the "Shop by theme"
 * tiles (our palette, not LEGO branding). Themes without an entry get a
 * stable colour from a fallback list, so a theme keeps its colour between
 * page views and deploys.
 */
final class ThemePalette
{
    /** slug => [accent, soft background] */
    private const COLORS = [
        'star-wars'           => ['#1f2a44', '#e8ebf2'],
        'city'                => ['#1e6fd9', '#e7f0fd'],
        'friends'             => ['#d63384', '#fbe7f1'],
        'ninjago'             => ['#c0392b', '#fbe9e7'],
        'duplo'               => ['#b97f00', '#fff4d6'],
        'creator'             => ['#f57c00', '#fff0e0'],
        'marvel-super-heroes' => ['#8e1b1b', '#f7e6e6'],
        'technic'             => ['#455a64', '#eaeef0'],
        'disney'              => ['#5c4fd6', '#ece9fb'],
        'education'           => ['#0f8a7e', '#e2f4f2'],
        'minecraft'           => ['#4c8f2f', '#e9f3e3'],
        'brickheadz'          => ['#a87d00', '#fff7d1'],
        'harry-potter'        => ['#7a1f2b', '#f5e6e8'],
        'icons'               => ['#1a1a2e', '#eceef3'],
        'speed-champions'     => ['#b71c1c', '#fbe8e8'],
        'botanicals'          => ['#2e7d32', '#e7f3e8'],
        'super-mario'         => ['#d32f2f', '#fdeaea'],
        'dc-comics-super-heroes' => ['#1c3f94', '#e6ecf8'],
        'jurassic-world'      => ['#5d7a1f', '#eef3e1'],
        'ideas'               => ['#b47d00', '#fff5d9'],
    ];

    private const FALLBACK = [
        ['#1e6fd9', '#e7f0fd'], ['#d63384', '#fbe7f1'], ['#0f8a7e', '#e2f4f2'],
        ['#f57c00', '#fff0e0'], ['#5c4fd6', '#ece9fb'], ['#4c8f2f', '#e9f3e3'],
        ['#c0392b', '#fbe9e7'], ['#455a64', '#eaeef0'],
    ];

    /** @return array{0: string, 1: string} [accent, soft background] */
    public static function forSlug(string $slug): array
    {
        return self::COLORS[$slug] ?? self::FALLBACK[crc32($slug) % count(self::FALLBACK)];
    }
}
