<?php

namespace frontend\components;

use common\components\IndexPolicy;
use common\enums\StatusEnum;
use common\models\Set;
use common\models\Tag;

/**
 * Hand-curated gift guides (/lego/gifts-for-<who>), listed on /lego/gifts.
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
        'gifts-for-kids'   => 'kids',
        'gifts-for-tweens' => 'tweens',
        'gifts-for-teens'  => 'teens',
        'gifts-for-adults' => 'adults',
        'flower-gifts'     => 'flowers',
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
     * Where generic "Gift ideas" links (menu, footer, homepage shortcut)
     * point: the /lego/gifts hub listing every guide.
     */
    /**
     * Pre-Christmas gift season (15 Nov - 24 Dec): the "Gifts" menu link is
     * highlighted and the homepage Christmas banner carries the guide picker.
     */
    public static function isGiftSeason(?\DateTimeImmutable $today = null): bool
    {
        $md = (int)($today ?? new \DateTimeImmutable('now'))->format('md');

        return $md >= 1115 && $md <= 1224;
    }

    public static function entryRoute(): array
    {
        return ['/lego/lego/gifts'];
    }

    /**
     * Up to $limit sets that represent a guide on the /lego/gifts hub: the
     * first set still on sale from each section, in section order.
     *
     * @param array<string, mixed> $guide
     * @return Set[]
     */
    public static function coverSets(array $guide, int $limit = 3): array
    {
        $numbers = [];
        foreach ($guide['sections'] as $section) {
            $numbers = array_merge($numbers, array_map('strval', array_keys($section['sets'])));
        }
        $onSale = self::loadSets($numbers);

        $covers = [];
        foreach ($guide['sections'] as $section) {
            foreach (array_keys($section['sets']) as $number) {
                if (isset($onSale[(string)$number])) {
                    $covers[] = $onSale[(string)$number];
                    break;
                }
            }
            if (count($covers) >= $limit) {
                break;
            }
        }

        return $covers;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function definitions(): array
    {
        return [
            'kids' => [
                'hint'        => T::tr('Fire engines, horses, Pokémon'),
                'ageLabel'    => T::tr('Ages 5–8'),
                'accent'      => '#0d6efd',
                'soft'        => '#e7f0ff',
                'slug'        => 'gifts-for-kids',
                'icon'        => 'ph ph-gift',
                'modifier'    => 'kids',
                'title'       => T::tr('LEGO gifts for kids (ages 5–8)'),
                'teaser'      => T::tr('Fire engines, horses, Pokémon and dinosaurs: sets a 6-year-old can build.'),
                'audience'    => 'for-kids',
                'audienceCta' => T::tr('See our hand-picked LEGO gifts for kids aged 5 to 8.'),
                'metaTitle'   => T::tr('LEGO gift ideas for kids aged 5 to 8 - BrickAtlas'),
                'description' => T::tr('Hand-picked LEGO sets for 5, 6, 7 and 8 year olds, grouped by interest: vehicles, animals, Pokémon, dinosaurs and Disney princesses, with tips on choosing the right size.'),
                'intro'       => '{n, plural, =1{# set} other{# sets}} that a child aged 5 to 8 can build with a little help, grouped by what they like. All of them are in the current LEGO range.', // translated in the view with the real count
                'wizard'      => ['recipient' => 'gift', 'profile' => 'child'],
                'tipsTitle'   => T::tr('How to choose LEGO for a 5–8 year old'),
                'tips'        => [
                    [T::tr('The age on the box is a minimum.'), T::tr('A 6+ set suits most six-year-olds. Sets marked 8+ or 9+ usually need an adult nearby, at least for the first build.')],
                    [T::tr('Up to about 300 pieces they can build alone.'), T::tr('That is one afternoon. 400–700 pieces is a weekend project, best built together.')],
                    [T::tr('At this age a set is a toy.'), T::tr('Vehicles that roll, buildings that open and minifigures to act out stories with matter more than display value.')],
                    [T::tr('Sets marked 4+ are very simple.'), T::tr('They use big starter pieces and take minutes to build. Great for a five-year-old, often too easy for a seven-year-old.')],
                ],
                'sections'    => [
                    [
                        'icon'  => 'ph ph-siren',
                        'title' => T::tr('Fire engines, police and big vehicles'),
                        'lead'  => T::tr('LEGO City is the classic first theme: trucks, rescue vehicles and people to play with.'),
                        'tags'  => ['police', 'fire', 'construction'],
                        'sets'  => [
                            '60499' => T::tr('An airport fire engine with a water cannon and plenty of details. Almost 700 pieces, so a good main present for a seven-year-old.'),
                            '60495' => T::tr('A recycling truck with sorting bins. Children see one on their own street, which makes it great for play.'),
                            '60479' => T::tr('A small police prisoner transport, about 200 pieces. Built in one sitting, good as a first set or an extra.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-horse',
                        'title' => T::tr('Horses, animals and Friends'),
                        'lead'  => T::tr('Friends sets are about animals, friendship and everyday places, with mini-dolls instead of minifigures.'),
                        'tags'  => ['horse', 'animals', 'pets'],
                        'sets'  => [
                            '42688' => T::tr('A horse stable with a riding area, horses and grooming accessories. For a child who loves horses, it is hard to beat.'),
                            '42701' => T::tr('A koala care centre with several animals. Over 500 pieces, so it can fill a whole weekend.'),
                            '42679' => T::tr('A bunny hotel with fewer than 200 pieces, rated 5+. A quick build for the youngest in this age group.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-game-controller',
                        'title' => T::tr('Pokémon and Minecraft'),
                        'lead'  => T::tr('Characters children know from games and cartoons. The new Pokémon sets are among the most wanted LEGO this year.'),
                        'tags'  => ['pokemon', 'video-game', 'minecraft'],
                        'sets'  => [
                            '72164' => T::tr('Pikachu\'s training house, rated 6+. A safe choice for any Pokémon fan, even one who has never built LEGO before.'),
                            '72162' => T::tr('Eevee and Lapras on a treasure hunt, about 600 pieces. More to build for an older Pokémon fan.'),
                            '21585' => T::tr('A chicken farm from Minecraft. A small, inexpensive set that fans of the game recognise straight away.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-planet',
                        'title' => T::tr('Dinosaurs and Star Wars'),
                        'lead'  => T::tr('Action, chases and big creatures from Jurassic World and Star Wars.'),
                        'tags'  => ['dinosaur', 'space'],
                        'sets'  => [
                            '77983' => T::tr('A mosasaurus attacking a boat, from "Jurassic World Rebirth". Over 700 pieces of adventure for a dinosaur fan.'),
                            '75423' => T::tr('Luke Skywalker\'s X-wing from the first Star Wars film, rated 6+. A young fan can build the famous starfighter.'),
                            '77977' => T::tr('A baby pteranodon built from almost 400 pieces. A smaller present for anyone who loves dinosaurs.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-crown',
                        'title' => T::tr('Disney princesses'),
                        'lead'  => T::tr('Castles and scenes from Disney films, with mini-doll princesses.'),
                        'tags'  => ['disney-princess', 'princess', 'castle'],
                        'sets'  => [
                            '43297' => T::tr('Rapunzel\'s castle from "Tangled", almost 700 pieces. A main present for a fan of Disney princesses.'),
                            '43299' => T::tr('Ariel\'s royal wedding from "The Little Mermaid". A scene to play out, about 500 pieces.'),
                            '43294' => T::tr('Rapunzel\'s mini tower with fewer than 200 pieces, rated 5+. A small first set or an extra present.'),
                        ],
                    ],
                ],
                'faq'         => [
                    [T::tr('Which LEGO is best for a 6-year-old?'), T::tr('Sets marked 5+ or 6+ with 200 to 500 pieces: City vehicles, Friends, Pokémon or Disney. Pick by what the child likes, because the theme matters more than the piece count.')],
                    [T::tr('Is LEGO Friends only for girls?'), T::tr('No. Friends is about animals, friends and everyday places, and many boys enjoy it too, just as many girls love City fire engines. Choose by interest, not by the colour of the box.')],
                    [T::tr('Can a 5-year-old build LEGO alone?'), T::tr('Small sets of up to about 200 pieces, yes. Bigger ones go better together: the adult finds the pieces and the child puts them on.')],
                ],
            ],
            'tweens' => [
                'hint'        => T::tr('Harry Potter, Technic, Minecraft'),
                'ageLabel'    => T::tr('Ages 9–12'),
                'accent'      => '#198754',
                'soft'        => '#e6f4ec',
                'slug'        => 'gifts-for-tweens',
                'icon'        => 'ph ph-gift',
                'modifier'    => 'tweens',
                'title'       => T::tr('LEGO gifts for kids aged 9–12'),
                'teaser'      => T::tr('Harry Potter, Technic, Minecraft and dragons: bigger builds for older kids.'),
                'audience'    => 'for-tweens',
                'audienceCta' => T::tr('See our hand-picked LEGO gifts for kids aged 9 to 12.'),
                'metaTitle'   => T::tr('LEGO gift ideas for kids aged 9 to 12 - BrickAtlas'),
                'description' => T::tr('Hand-picked LEGO sets for 9, 10, 11 and 12 year olds, grouped by interest: Harry Potter, Technic, Minecraft, dragons and creative builds, with buying tips.'),
                'intro'       => '{n, plural, =1{# set} other{# sets}} for kids aged 9 to 12 who are ready for bigger, more detailed builds. All of them are in the current LEGO range.', // translated in the view with the real count
                'wizard'      => ['recipient' => 'gift', 'profile' => 'older_child'],
                'tipsTitle'   => T::tr('How to choose LEGO for a 9–12 year old'),
                'tips'        => [
                    [T::tr('They can build on their own now.'), T::tr('Most children of this age follow instructions for 500 to 1,000 pieces without help, so the set can be bigger than before.')],
                    [T::tr('Technic is the next step.'), T::tr('Gears, axles and moving parts, for kids who like to know how things work.')],
                    [T::tr('A favourite film or game wins.'), T::tr('A Harry Potter or Minecraft fan will be happier with a set from that world than with a random big box.')],
                    [T::tr('Check the size.'), T::tr('Many sets for this age are also models to display. Make sure there is shelf space before buying a 1,500-piece castle.')],
                ],
                'sections'    => [
                    [
                        'icon'  => 'ph ph-magic-wand',
                        'title' => T::tr('For the Harry Potter fan'),
                        'lead'  => T::tr('Magical creatures, wizarding shops and Hogwarts itself, from a small figure to a big castle.'),
                        'tags'  => ['harry-potter', 'hogwarts', 'castle'],
                        'sets'  => [
                            '76477' => T::tr('Norbert, Hagrid\'s baby dragon, as a large buildable figure. A smaller present that every Harry Potter reader will recognise.'),
                            '76471' => T::tr('The dark shops of Knockturn Alley from "Half-Blood Prince". Almost 800 pieces with several minifigures.'),
                            '76473' => T::tr('The east wing of Hogwarts from "Chamber of Secrets", over 2,100 pieces. A main present, or a joint one from the whole family.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-gear',
                        'title' => T::tr('Technic cars and motorbikes'),
                        'lead'  => T::tr('Real vehicles with working steering and moving parts, for kids who like mechanics.'),
                        'tags'  => ['car', 'motorcycle'],
                        'sets'  => [
                            '42238' => T::tr('A Ducati motocross bike, about 450 pieces. A good first Technic set.'),
                            '42227' => T::tr('A Jeep Wrangler Rubicon with working steering, about 700 pieces. Fun to build and to drive around the room.'),
                            '42241' => T::tr('The Bugatti Chiron Pur Sport, about 770 pieces. A supercar for a young car fan that also looks good on a shelf.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-game-controller',
                        'title' => T::tr('Minecraft and Pokémon'),
                        'lead'  => T::tr('Bigger sets from the games kids of this age play most.'),
                        'tags'  => ['minecraft', 'video-game', 'pokemon'],
                        'sets'  => [
                            '21596' => T::tr('An Evoker attack on a Minecraft village, about 600 pieces with several characters.'),
                            '21597' => T::tr('A Ghast station from Minecraft, over 750 pieces. One of the biggest new Minecraft sets.'),
                            '72163' => T::tr('Mewtwo\'s lab break, rated 10+. A bigger Pokémon set for an older fan.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-sword',
                        'title' => T::tr('Ninjas, dragons and pirates'),
                        'lead'  => T::tr('Big builds full of action: Ninjago ships and battles, and a pirate ship that turns into other models.'),
                        'tags'  => ['ninja', 'dragon', 'pirate'],
                        'sets'  => [
                            '71871' => T::tr('A Ninjago battle at the Dragon\'s Blade, over 1,000 pieces. A main present for a Ninjago fan.'),
                            '71869' => T::tr('The Land Bounty, the ninjas\' big ship from Ninjago Dragons Rising, over 1,200 pieces.'),
                            '31387' => T::tr('A pirate ship from the Creator 3-in-1 range, over 1,000 pieces. The same pieces build two other models, so it lasts longer.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-paint-brush',
                        'title' => T::tr('Friends and creative builds'),
                        'lead'  => T::tr('Detailed buildings and decorative models for kids who like to design and decorate.'),
                        'tags'  => ['3-in-1', 'musical-instrument'],
                        'sets'  => [
                            '42704' => T::tr('The Grand Hotel in Heartlake City, over 1,400 pieces. The biggest new Friends set, with lots of rooms to furnish.'),
                            '42705' => T::tr('A cosy cabin in an autumn forest, over 1,200 pieces. A calmer, detailed build.'),
                            '31392' => T::tr('A decorative piano from the Creator 3-in-1 range, under 500 pieces. It can be rebuilt into two other models.'),
                        ],
                    ],
                ],
                'faq'         => [
                    [T::tr('Which LEGO is best for a 10-year-old?'), T::tr('Sets marked 9+ or 10+ with 500 to 1,000 pieces. Harry Potter, Minecraft, Technic and Ninjago are the most popular choices at this age.')],
                    [T::tr('Is Technic too hard for a 10-year-old?'), T::tr('Not if they have built LEGO before. Start with a smaller Technic set of 400 to 800 pieces; the big supercars are rated 18+ for a reason.')],
                    [T::tr('Can a 12-year-old build an 18+ set?'), T::tr('Often yes, with some patience. The 18+ label is mostly about the theme and the length of the build. Our guide for teenagers has more ideas.')],
                ],
            ],
            'teens' => [
                'hint'        => T::tr('Star Wars, cars, games'),
                'ageLabel'    => T::tr('Ages 13–16'),
                'accent'      => '#6f42c1',
                'soft'        => '#f0eafa',
                'slug'        => 'gifts-for-teens',
                'icon'        => 'ph ph-gift',
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
                        'icon'  => 'ph ph-sparkle',
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
                        'icon'  => 'ph ph-speedometer',
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
                        'icon'  => 'ph ph-game-controller',
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
                        'icon'  => 'ph ph-house-line',
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
                'hint'        => T::tr('Films, flowers, big builds'),
                'ageLabel'    => T::tr('Adults'),
                'accent'      => '#d01012',
                'soft'        => '#fde8e8',
                'slug'        => 'gifts-for-adults',
                'icon'        => 'ph ph-gift',
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
                        'icon'  => 'ph ph-film-strip',
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
                        'icon'  => 'ph ph-flower',
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
                        'icon'  => 'ph ph-lamp',
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
                        'icon'  => 'ph ph-game-controller',
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
                        'icon'  => 'ph ph-buildings',
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
            'flowers' => [
                'hint'        => T::tr('Bouquets, orchids, bonsai'),
                'ageLabel'    => T::tr('Flowers'),
                'accent'      => '#c2185b',
                'soft'        => '#fce4ef',
                'slug'        => 'flower-gifts',
                'icon'        => 'ph ph-flower',
                'modifier'    => 'flowers',
                'title'       => T::tr('LEGO flowers as a gift'),
                'teaser'      => T::tr('Bouquets, orchids and bonsai that never wilt, from small sets to a flower wall.'),
                'audience'    => null,
                'audienceCta' => '',
                'metaTitle'   => T::tr('LEGO flowers: bouquets, orchids and plants to give - BrickAtlas'),
                'description' => T::tr('Hand-picked LEGO flower sets to give as a present: bouquets of roses, tulips and sunflowers, small orchids, potted plants and bonsai, with tips on vases, build time and size.'),
                'intro'       => '{n, plural, =1{# flower set} other{# flower sets}} that look good in any home and never need watering. All of them are in the current LEGO range.', // translated in the view with the real count
                'wizard'      => ['recipient' => 'gift', 'profile' => 'adult'],
                'tipsTitle'   => T::tr('How to choose LEGO flowers as a gift'),
                'tips'        => [
                    [T::tr('They never wilt.'), T::tr('A LEGO bouquet looks the same after a year as on the first day, which is why it works for birthdays, anniversaries and as a thank-you.')],
                    [T::tr('18+ does not mean difficult.'), T::tr('The label is about who the set is for. Flower sets are calm, repetitive builds that a complete beginner can finish in an evening or two.')],
                    [T::tr('Bouquets usually come without a vase.'), T::tr('Plan a vase or a tall glass for a bouquet. Potted plants such as the orchid, the peace lily or the bonsai come with their pot.')],
                    [T::tr('Small sets can be combined.'), T::tr('Daisies, a mini orchid or a small bouquet are inexpensive extras, and several of them together make a fuller arrangement.')],
                ],
                'sections'    => [
                    [
                        'icon'  => 'ph ph-flower-tulip',
                        'title' => T::tr('Bouquets'),
                        'lead'  => T::tr('Classic cut flowers in brick form: roses, tulips and sunflowers.'),
                        'tags'  => ['botanical-collection', 'brick-built-plants'],
                        'sets'  => [
                            '10374' => T::tr('A bouquet of pink roses, almost 800 pieces. The most obvious choice for Valentine\'s Day, an anniversary or Mother\'s Day.'),
                            '11501' => T::tr('A tulip bouquet in spring colours, about 580 pieces. A cheerful present that suits any occasion.'),
                            '11502' => T::tr('Sunflowers, about 690 pieces. Large, bright flowers that brighten up a room even in winter.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-plant',
                        'title' => T::tr('Plants for the home and desk'),
                        'lead'  => T::tr('Potted plants that come with their pot and look good on a shelf, a windowsill or at work.'),
                        'tags'  => ['brick-built-plants'],
                        'sets'  => [
                            '11512' => T::tr('A hanging golden pothos, about 370 pieces. Looks like the real plant and needs no care at all.'),
                            '11504' => T::tr('A peace lily in a pot, under 500 pieces. A calm, elegant plant for a living room or an office.'),
                            '10373' => T::tr('Mini bonsai trees, about 700 pieces. A small Japanese garden for a desk.'),
                            '10370' => T::tr('A poinsettia, the classic Christmas plant, about 600 pieces. A festive decoration that lasts much longer than the real one.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-gift',
                        'title' => T::tr('Small flowers for a smaller budget'),
                        'lead'  => T::tr('Quick builds of up to about 400 pieces: good as an extra present or a first LEGO set.'),
                        'tags'  => ['brick-built-plants'],
                        'sets'  => [
                            '10343' => T::tr('A mini orchid in a pot, under 300 pieces. An elegant little present that is built in about an hour.'),
                            '11508' => T::tr('Daisies, about 130 pieces, rated 9+. The cheapest set in this guide and a nice add-on to another present.'),
                            '10347' => T::tr('A petite sunny bouquet, under 400 pieces. Bright colours for a small budget.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-frame-corners',
                        'title' => T::tr('Big arrangements'),
                        'lead'  => T::tr('Larger compositions for the main present, or for someone who already has a few LEGO flowers.'),
                        'tags'  => ['botanical-collection'],
                        'sets'  => [
                            '10345' => T::tr('A large flower arrangement, over 1,100 pieces. A centrepiece for the table and a longer build.'),
                            '11503' => T::tr('A flower wall, almost 900 pieces. A decorative panel instead of a bouquet in a vase.'),
                            '11513' => T::tr('A flower arrangement in dark, deep colours, about 560 pieces. An elegant choice for someone who does not like pastels.'),
                        ],
                    ],
                    [
                        'icon'  => 'ph ph-smiley',
                        'title' => T::tr('Flowers for younger builders'),
                        'lead'  => T::tr('Sets rated 8+ and 9+, for children and teenagers who like plants and decorating their room.'),
                        'tags'  => ['3-in-1'],
                        'sets'  => [
                            '10371' => T::tr('Cosy plants with cheerful faces, about 250 pieces. One of the highest-rated sets of the year.'),
                            '11509' => T::tr('A flowering cactus, under 500 pieces, rated 9+. A fun decoration for a desk or a windowsill.'),
                            '31383' => T::tr('A perfume bottle with flowers from the Creator 3-in-1 range. The same pieces build two other models.'),
                        ],
                    ],
                ],
                'faq'         => [
                    [T::tr('Do LEGO bouquets come with a vase?'), T::tr('Most bouquets do not, so plan a vase or a tall glass. Potted plants such as the orchid, the peace lily or the bonsai come with a brick-built pot.')],
                    [T::tr('How long does it take to build a LEGO bouquet?'), T::tr('A set of about 300 pieces takes an hour or so, 600 to 800 pieces two to three hours, and the biggest arrangements an evening or two.')],
                    [T::tr('Are LEGO flowers a good gift for someone who has never built LEGO?'), T::tr('Yes. The instructions are picture-based and step by step, and many people who receive a bouquet go on to collect the whole series.')],
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
