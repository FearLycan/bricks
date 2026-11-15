<?php

namespace frontend\components;

use common\components\IndexPolicy;
use common\enums\StatusEnum;
use common\models\Set;
use common\models\Tag;

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
        'gifts-for-teens'  => 'teens',
        'gifts-for-adults' => 'adults',
    ];

    /**
     * The guide linked from an audience listing (/lego/for-teens etc.), or null.
     *
     * @return array<string, mixed>|null
     */
    public static function forAudience(string $audienceSlug): ?array
    {
        foreach (self::definitions() as $guide) {
            if (($guide['audience'] ?? null) === $audienceSlug) {
                return $guide;
            }
        }

        return null;
    }

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
                'audience'    => 'for-teens',
                'audienceCta' => T::tr('See our hand-picked LEGO gifts for teens.'),
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
                        'tags'  => ['the-mandalorian-series', 'bounty-hunters'],
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
                        'tags'  => ['car', 'large-scale-vehicle'],
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
                        'tags'  => ['video-game', 'gaming'],
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
                        'tags'  => ['display-stand', '18-plus'],
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
            'adults' => [
                'slug'        => 'gifts-for-adults',
                'icon'        => 'bi-gift',
                'modifier'    => 'adults',
                'title'       => T::tr('LEGO gifts for adults'),
                'teaser'      => T::tr('Sets for film fans, gamers, plant lovers and big builds.'),
                'audience'    => 'for-adults',
                'audienceCta' => T::tr('See our hand-picked LEGO gifts for adults.'),
                'metaTitle'   => T::tr('LEGO gift ideas for adults - BrickAtlas'),
                'description' => T::tr('Hand-picked LEGO sets for adults, grouped by interest: films, plants, desk models, video games and big builds, with tips on choosing the right one.'),
                'intro'       => '{n, plural, =1{# set} other{# sets}} for adults who love films, games, plants or big projects. All of them are in the current LEGO range.', // translated in the view with the real count
                'wizard'      => ['recipient' => 'gift', 'profile' => 'adult'],
                'tipsTitle'   => T::tr('How to choose LEGO for an adult'),
                'tips'        => [
                    [T::tr('Look at their passions, not the toy aisle.'), T::tr('Film fans, gamers and gardeners all have a LEGO set made for them.')],
                    [T::tr('Think about where it will stand.'), T::tr('Most 18+ sets are display models, so size and style matter as much as the theme.')],
                    [T::tr('It does not have to be expensive.'), T::tr('For 50–80 USD you get a set for an evening or two that looks great on a shelf.')],
                    [T::tr('A big set is time for yourself.'), T::tr('Over 3,000 pieces is a project for the whole holiday break or several weeks of evenings.')],
                ],
                'sections'    => [
                    [
                        'icon'  => 'bi-film',
                        'title' => T::tr('For the film fan'),
                        'lead'  => T::tr('Scenes and vehicles from films people grew up with.'),
                        'tags'  => ['1980s', '1990s'],
                        'sets'  => [
                            '21371' => T::tr('Wallace and Gromit\'s house with their famous inventions. A warm, funny model for anyone who loves British animation.'),
                            '21370' => T::tr('E.T. on the bike against the moon, the best-known scene from the film. A present with plenty of nostalgia.'),
                            '76355' => T::tr('The Batmobile from Tim Burton\'s "Batman Returns", with over 2,200 pieces. A big display model for a Batman fan.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-flower1',
                        'title' => T::tr('Flowers that never wilt'),
                        'lead'  => T::tr('Botanical sets look good in any home and never need watering.'),
                        'tags'  => ['brick-built-plants', 'botanical-collection'],
                        'sets'  => [
                            '11512' => T::tr('A hanging pothos in a pot: a small, quick build that looks like the real plant. A safe pick when you are not sure what to buy.'),
                            '11513' => T::tr('A bouquet in dark, deep colours. A more elegant alternative to fresh flowers.'),
                            '11505' => T::tr('Woodland mushrooms with moss and small details. An unusual decoration for a shelf or desk.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-lamp',
                        'title' => T::tr('For the desk'),
                        'lead'  => T::tr('Small and medium models that fit on a desk or a shelf at work.'),
                        'tags'  => ['display-stand', 'space'],
                        'sets'  => [
                            '75439' => T::tr('A Darth Vader bust with under 350 pieces. A Star Wars gadget for one evening and a modest budget.'),
                            '11382' => T::tr('The Hubble Space Telescope on a stand with a plaque. For someone into space and science.'),
                            '11381' => T::tr('The classic Jaguar E-Type in over 1,600 pieces. A present for a fan of old cars and good design.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-controller',
                        'title' => T::tr('For the gamer'),
                        'lead'  => T::tr('Consoles and arcade machines for people who remember them from childhood.'),
                        'tags'  => ['video-game', 'nintendo'],
                        'sets'  => [
                            '72306' => T::tr('The PlayStation console in LEGO, over 1,900 pieces. A strong pick for anyone who grew up with games.'),
                            '72051' => T::tr('A Donkey Kong arcade cabinet. Retro gaming in brick form that looks great in a gaming corner.'),
                            '77093' => T::tr('The final battle from "Ocarina of Time" in brick form. The cheapest set in this section, and a scene every Zelda fan knows by heart.'),
                        ],
                    ],
                    [
                        'icon'  => 'bi-buildings',
                        'title' => T::tr('A big project for winter evenings'),
                        'lead'  => T::tr('Thousands of pieces and weeks of building, for someone who likes to take their time.'),
                        'tags'  => ['real-place', 'fine-arts'],
                        'sets'  => [
                            '21067' => T::tr('London\'s Tower Bridge in 3,745 pieces. A large Architecture model that keeps a builder busy for many evenings.'),
                            '21373' => T::tr('Downton Abbey in 4,711 pieces, with characters from the series. A main present for a fan of period dramas.'),
                            '31221' => T::tr('Gustav Klimt\'s "The Kiss" in 4,000 pieces. A picture to hang on the wall, and building it feels like laying a mosaic.'),
                        ],
                    ],
                ],
                'faq'         => [
                    [T::tr('Is LEGO a good gift for an adult?'), T::tr('Yes. LEGO has a whole 18+ range: display models, flowers and replicas of famous vehicles and buildings. Many adults build to relax.')],
                    [T::tr('How much should I spend on a LEGO set for an adult?'), T::tr('Sets for 50–150 USD make a good present. The biggest models cost more and work best as a main or joint gift.')],
                    [T::tr('What if I do not know which sets they already have?'), T::tr('Pick a release from the last few months, which they are less likely to own, or go for flowers, which few people collect in full.')],
                ],
            ],
        ];
    }

    /**
     * Tags linked from a guide's sections, keyed by slug. Only active tags
     * whose listing is indexable (IndexPolicy::MIN_TAG_SETS) are returned,
     * so the guide never links to a thin, noindexed tag page.
     *
     * @param string[] $slugs
     * @return array<string, Tag>
     */
    public static function loadTags(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        return Tag::find()
            ->where(['slug' => array_values(array_unique($slugs)), 'status' => StatusEnum::ACTIVE->value])
            ->andWhere(['>=', 'sets_count', IndexPolicy::MIN_TAG_SETS])
            ->indexBy('slug')
            ->all();
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
