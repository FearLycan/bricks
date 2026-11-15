<?php

namespace frontend\components;

use common\enums\StatusEnum;
use common\models\Set;

/**
 * Hand-curated gift guides (/lego/gifts-for-<who>).
 *
 * Each guide is a short editorial page: buying tips, a few sections of
 * hand-picked sets with one or two sentences on why each fits, and an FAQ.
 * Sets are picked by number; anything no longer on sale (not released yet or
 * past its retirement date) is skipped at render time, so the page never
 * recommends a set people cannot buy. Swap retired picks here.
 *
 * All copy goes through T::tr() (English source, translations in
 * frontend/messages/<lang>/app.php).
 */
final class GiftGuide
{
    /** URL slug => guide key. */
    public const SLUGS = [
        'gifts-for-teens' => 'teens',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        $guides = self::definitions();

        return $guides[$key] ?? null;
    }

    /**
     * All guides in display order (homepage strip, menu, footer).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::definitions();
    }

    /**
     * Where generic "Gift ideas" links (menu, footer) point. With a single
     * guide that is the guide itself; once there are several, point this at
     * a listing page instead.
     */
    public static function entryRoute(): array
    {
        $first = array_values(self::definitions())[0];

        return ['/lego/lego/gift-guide', 'slug' => $first['slug']];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function definitions(): array
    {
        return [
            'teens' => [
                'slug'        => 'gifts-for-teens',
                'icon'        => 'bi-gift',
                'modifier'    => 'teens',
                'title'       => T::tr('LEGO gifts for teens (ages 13–16)'),
                'teaser'      => T::tr('Sets picked by interest: Star Wars, cars, games and display models.'),
                'metaTitle'   => T::tr('LEGO gift ideas for teens (13–16) - BrickAtlas'),
                'description' => T::tr('Hand-picked LEGO sets for teenagers aged 13 to 16, grouped by interest: Star Wars, cars, video games and display models, with tips on choosing the right one.'),
                'intro'       => '{n, plural, =1{# set} other{# sets}} picked for their interests, not just the age on the box. All of them are in the current LEGO range.', // translated in the view with the real count
                'wizard'      => ['recipient' => 'gift', 'profile' => 'teen'],
                'tipsTitle'   => T::tr('How to choose LEGO for a teenager'),
                'tips'        => [
                    [T::tr('Start with their interests, not their age.'), T::tr('A teenager will be happier with a Batmobile or a console from a favourite game than with an "ages 14+" set that has nothing to do with what they like.')],
                    [T::tr('18+ does not mean too hard.'), T::tr('It usually means a longer build and a model meant for display. Anyone who has built LEGO before will manage.')],
                    [T::tr('Piece count is build time.'), T::tr('300–500 pieces is one evening, 700–1,000 a few evenings, and over 1,500 is a project for several days of the holiday break.')],
                    [T::tr('To play with or to display?'), T::tr('Younger teens more often play with vehicles and minifigures; older ones prefer a model for the desk or shelf.')],
                ],
                'sections'    => [
                    [
                        'icon'  => 'bi-stars',
                        'title' => T::tr('For the Star Wars fan'),
                        'lead'  => T::tr('A safe choice if they watch "The Mandalorian" or the other series set in that galaxy.'),
                        'sets'  => [
                            '75447' => T::tr('The Mandalorian\'s ship from the series most teens know. Over 900 pieces, yet still sturdy enough to play with, not only to display.'),
                            '75459' => T::tr('An Imperial Remnant shuttle from "The Mandalorian", rated 14+. A longer, calmer build for someone who likes detail.'),
                            '75453' => T::tr('The biggest of the three: 1,683 pieces, the Jawa sandcrawler and the Mudhorn from "The Mandalorian". A good pick for the main present.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-speedometer2',
                        'title' => T::tr('For the car fan'),
                        'lead'  => T::tr('From small display cars to Technic mechanics with working parts.'),
                        'sets'  => [
                            '42235' => T::tr('Technic means mechanics instead of studs. A supercar model with moving parts and about 900 pieces.'),
                            '42239' => T::tr('The Batmobile from Christopher Nolan\'s trilogy. It covers two passions at once: cars and comics.'),
                            '77263' => T::tr('Speed Champions are small, detailed cars. With 358 pieces this one works as an extra or a smaller present.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-controller',
                        'title' => T::tr('For the gamer'),
                        'lead'  => T::tr('Sets from games teenagers know: Nintendo, Minecraft and Sonic.'),
                        'sets'  => [
                            '72046' => T::tr('A replica of the console from the 18+ range that a teenager will easily build. Great on a desk, especially for a retro gaming fan.'),
                            '21595' => T::tr('The Ender Dragon from Minecraft: recognisable to every player and big enough to last longer than one evening.'),
                            '77006' => T::tr('A big truck from the Sonic range with several characters. More for play than display, good for the younger end of this age group.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-house-heart',
                        'title' => T::tr('For the shelf: ambitious builds'),
                        'lead'  => T::tr('Models that stay on display after the build instead of going back in the box.'),
                        'sets'  => [
                            '76478' => T::tr('A book nook that slots between books on a shelf, with Dumbledore\'s office. A great present for a Harry Potter fan who reads.'),
                            '21066' => T::tr('The New York skyline from the Architecture range. A smart desk model for someone into travel or design.'),
                            '76352' => T::tr('A set from the film "Avengers: Doomsday". A fresh release with almost 1,000 pieces.'),
                        ],
                    ],
                ],
                'faq'         => [
                    [T::tr('Can a 14-year-old build an 18+ set?'), T::tr('Yes, if they enjoy building and have some patience. LEGO instructions are picture-based and step by step, and the 18+ label is mostly about the theme and the length of the build.')],
                    [T::tr('How much should I spend on a LEGO gift for a teenager?'), T::tr('Sets with 300 to 1,000 pieces are a good starting point. Smaller ones work as an extra, bigger ones as the main present.')],
                    [T::tr('What if the set sells out before Christmas?'), T::tr('Every set page shows whether its retirement is coming up. Popular models are worth buying early.')],
                ],
            ],
        ];
    }

    /**
     * Sets for a guide, keyed by number, in the guide's order; only sets that
     * are active, released and not past their retirement date.
     *
     * @param string[] $numbers
     * @return array<string, Set>
     */
    public static function loadSets(array $numbers): array
    {
        $found = Set::find()
            ->with(['theme'])
            ->where(['number' => $numbers, 'status' => StatusEnum::ACTIVE->value])
            ->indexBy('number')
            ->all();

        $today = date('Y-m-d');
        $sets = [];
        foreach ($numbers as $number) {
            $set = $found[$number] ?? null;
            if ($set === null || !$set->isReleased()) {
                continue;
            }
            if ($set->exit_date !== null && $set->exit_date !== '' && $set->exit_date < $today) {
                continue;
            }
            $sets[$number] = $set;
        }

        return $sets;
    }
}
