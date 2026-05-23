<?php

namespace frontend\components;

/**
 * Source of truth for the public LEGO glossary at /glossary.
 *
 * The page is a pure read view, so the data lives in code: adding a new term
 * means appending one row to a category's `terms` array. All user-facing
 * strings go through T::tr so EN and PL translations stay in app.php.
 *
 * Term schema:
 *   - term:       short label rendered as the heading (acronym or short name)
 *   - full:       optional expansion (e.g. "Adult Fan of LEGO" for AFOL)
 *   - definition: full sentence(s) explaining the concept
 */
final class LegoGlossary
{
    /**
     * @return array<int, array{slug:string,name:string,icon:string,intro:string,terms:array<int,array{term:string,full:?string,definition:string}>}>
     */
    public static function getCategories(): array
    {
        return [
            [
                'slug'  => 'community',
                'name'  => T::tr('Community'),
                'icon'  => 'bi-people-fill',
                'intro' => T::tr('Labels fans use to identify themselves and the groups they build with.'),
                'terms' => [
                    [
                        'term'       => 'AFOL',
                        'full'       => 'Adult Fan of LEGO',
                        'definition' => T::tr('An adult LEGO enthusiast — typically 18+ — who actively builds, collects, or follows the hobby.'),
                    ],
                    [
                        'term'       => 'TFOL',
                        'full'       => 'Teen Fan of LEGO',
                        'definition' => T::tr('A teenage LEGO fan, usually 13–17. The bridge between KFOL and AFOL.'),
                    ],
                    [
                        'term'       => 'KFOL',
                        'full'       => 'Kid Fan of LEGO',
                        'definition' => T::tr('A child LEGO fan, the original target audience for most retail sets.'),
                    ],
                    [
                        'term'       => 'AFFOL',
                        'full'       => 'Adult Female Fan of LEGO',
                        'definition' => T::tr('An adult female LEGO fan. Used when distinguishing the demographic; otherwise AFOL covers everyone.'),
                    ],
                    [
                        'term'       => 'LUG',
                        'full'       => 'LEGO User Group',
                        'definition' => T::tr('A community of AFOLs that meets locally or online to build, exhibit, and share the hobby.'),
                    ],
                    [
                        'term'       => 'RLUG',
                        'full'       => 'Recognized LEGO User Group',
                        'definition' => T::tr('A LUG officially recognized by The LEGO Group, eligible for support like exclusive sets for events.'),
                    ],
                    [
                        'term'       => 'LAN',
                        'full'       => 'LEGO Ambassador Network',
                        'definition' => T::tr('The official channel through which TLG communicates with recognized LUGs and ambassadors worldwide.'),
                    ],
                    [
                        'term'       => 'TLG',
                        'full'       => 'The LEGO Group',
                        'definition' => T::tr('The Danish company that designs and manufactures LEGO products.'),
                    ],
                ],
            ],
            [
                'slug'  => 'parts',
                'name'  => T::tr('Parts & Bricks'),
                'icon'  => 'bi-grid-3x3-gap-fill',
                'intro' => T::tr('Names for the individual elements you actually snap together.'),
                'terms' => [
                    [
                        'term'       => 'Brick',
                        'full'       => null,
                        'definition' => T::tr('The classic rectangular element with studs on top — the building block of every LEGO set (e.g. 2x4, 1x2).'),
                    ],
                    [
                        'term'       => 'Plate',
                        'full'       => null,
                        'definition' => T::tr('A flat element one third of a brick’s height. Three stacked plates equal the height of one brick.'),
                    ],
                    [
                        'term'       => 'Tile',
                        'full'       => null,
                        'definition' => T::tr('A flat element with no studs on top, used for smooth surfaces, floors, and details.'),
                    ],
                    [
                        'term'       => 'Stud',
                        'full'       => null,
                        'definition' => T::tr('The cylindrical bump on top of a brick or plate that allows pieces to snap together.'),
                    ],
                    [
                        'term'       => 'Slope',
                        'full'       => null,
                        'definition' => T::tr('An angled element used for roofs, ramps, and curved surfaces.'),
                    ],
                    [
                        'term'       => 'Cheese slope',
                        'full'       => null,
                        'definition' => T::tr('A tiny 1x1 sloped piece named for its wedge shape; a workhorse for greebling and small details.'),
                    ],
                    [
                        'term'       => 'Jumper',
                        'full'       => null,
                        'definition' => T::tr('A plate with a single centered stud, used to offset other parts by half a stud.'),
                    ],
                    [
                        'term'       => 'Minifig',
                        'full'       => 'Minifigure',
                        'definition' => T::tr('The iconic 4 cm LEGO figure introduced in 1978. Customizable through swapping heads, torsos, and legs.'),
                    ],
                    [
                        'term'       => 'Baseplate',
                        'full'       => null,
                        'definition' => T::tr('A large thin plate that serves as the foundation for a build. Not stackable like regular plates.'),
                    ],
                    [
                        'term'       => 'Polybag',
                        'full'       => null,
                        'definition' => T::tr('A small set (usually under 100 pieces) sold in a sealed plastic bag instead of a box. Often promotional.'),
                    ],
                ],
            ],
            [
                'slug'  => 'sets',
                'name'  => T::tr('Sets & Themes'),
                'icon'  => 'bi-box-seam-fill',
                'intro' => T::tr('Labels for set lines, exclusives, and lifecycle stages.'),
                'terms' => [
                    [
                        'term'       => 'UCS',
                        'full'       => 'Ultimate Collector Series',
                        'definition' => T::tr('A Star Wars line of large, display-focused sets aimed at adult collectors, often with high piece counts.'),
                    ],
                    [
                        'term'       => 'D2C',
                        'full'       => 'Direct to Consumer',
                        'definition' => T::tr('A premium set distributed mainly via LEGO.com and brand stores rather than mass-market retailers.'),
                    ],
                    [
                        'term'       => 'GWP',
                        'full'       => 'Gift with Purchase',
                        'definition' => T::tr('A small bonus set given free with qualifying orders, typically time-limited and used to drive sales.'),
                    ],
                    [
                        'term'       => 'VIP',
                        'full'       => 'LEGO VIP',
                        'definition' => T::tr('LEGO’s free loyalty program: earn points on purchases, redeem for sets, GWPs, or experiences.'),
                    ],
                    [
                        'term'       => 'Modular',
                        'full'       => 'Modular Buildings',
                        'definition' => T::tr('An adult-oriented line of stackable city buildings (32x32 stud footprint) that connect side by side.'),
                    ],
                    [
                        'term'       => 'Magazine gift',
                        'full'       => null,
                        'definition' => T::tr('A mini-build polybag bundled with a collectible LEGO magazine at newsstands and kiosks.'),
                    ],
                    [
                        'term'       => 'Exclusive',
                        'full'       => null,
                        'definition' => T::tr('A set sold only through official LEGO channels (LEGO.com, brand stores) and not via mass retailers.'),
                    ],
                    [
                        'term'       => 'Retired',
                        'full'       => null,
                        'definition' => T::tr('A set officially out of production. Stock dries up and aftermarket prices tend to climb.'),
                    ],
                    [
                        'term'       => 'EOL',
                        'full'       => 'End of Life',
                        'definition' => T::tr('A set scheduled for retirement. Once EOL is reached, no more units will be produced.'),
                    ],
                    [
                        'term'       => 'Promotional',
                        'full'       => null,
                        'definition' => T::tr('A small set distributed as a giveaway, contest prize, or partnership bonus rather than retail sale.'),
                    ],
                ],
            ],
            [
                'slug'  => 'techniques',
                'name'  => T::tr('Building Techniques'),
                'icon'  => 'bi-tools',
                'intro' => T::tr('Vocabulary for how AFOLs actually put bricks together.'),
                'terms' => [
                    [
                        'term'       => 'MOC',
                        'full'       => 'My Own Creation',
                        'definition' => T::tr('A model designed and built from scratch by a fan, not based on any official LEGO set.'),
                    ],
                    [
                        'term'       => 'MOD',
                        'full'       => 'Modification',
                        'definition' => T::tr('A change to an official set — adding details, extending baseplates, or remixing parts.'),
                    ],
                    [
                        'term'       => 'SNOT',
                        'full'       => 'Studs Not On Top',
                        'definition' => T::tr('A family of techniques that orient studs sideways or downward, used for smooth surfaces and unusual angles.'),
                    ],
                    [
                        'term'       => 'POOP',
                        'full'       => 'Pieces of Other Pieces',
                        'definition' => T::tr('A pre-assembled element (like a pre-printed wall section) replacing what builders would rather construct themselves. Generally seen as a negative.'),
                    ],
                    [
                        'term'       => 'BURP',
                        'full'       => 'Big Ugly Rock Piece',
                        'definition' => T::tr('A large, single-mold rock element. Disliked because it removes the chance to build rocks from smaller parts.'),
                    ],
                    [
                        'term'       => 'LURP',
                        'full'       => 'Little Ugly Rock Piece',
                        'definition' => T::tr('The smaller cousin of BURP — a single-piece rock mold for compact scenes.'),
                    ],
                    [
                        'term'       => 'Greebling',
                        'full'       => null,
                        'definition' => T::tr('Adding small decorative parts (pipes, antennas, gears) to a surface for a busy, technical look — popular on spaceships.'),
                    ],
                    [
                        'term'       => 'Microscale',
                        'full'       => null,
                        'definition' => T::tr('Building at a scale much smaller than minifigure scale, e.g. a whole castle that fits in your palm.'),
                    ],
                    [
                        'term'       => 'Minifigure scale',
                        'full'       => null,
                        'definition' => T::tr('The default scale (roughly 1:42) where models are sized for minifigures to interact with.'),
                    ],
                    [
                        'term'       => 'NPU',
                        'full'       => 'Nice Parts Usage',
                        'definition' => T::tr('A compliment for using a part in a clever, unexpected way — a wrench as a moustache, a flame as a fish tail.'),
                    ],
                    [
                        'term'       => 'Illegal technique',
                        'full'       => null,
                        'definition' => T::tr('A connection that works but violates LEGO’s internal design rules (e.g. stressing a part). Common in fan MOCs, rare in official sets.'),
                    ],
                ],
            ],
            [
                'slug'  => 'marketplace',
                'name'  => T::tr('Marketplace'),
                'icon'  => 'bi-shop',
                'intro' => T::tr('Terms you’ll see on resale sites, auction listings, and trade forums.'),
                'terms' => [
                    [
                        'term'       => 'BL',
                        'full'       => 'BrickLink',
                        'definition' => T::tr('The largest online marketplace for LEGO parts, minifigures, and sets, also home to the Stud.io builder.'),
                    ],
                    [
                        'term'       => 'BO',
                        'full'       => 'BrickOwl',
                        'definition' => T::tr('An online LEGO marketplace and inventory tool — a smaller alternative to BrickLink.'),
                    ],
                    [
                        'term'       => 'MISB',
                        'full'       => 'Mint In Sealed Box',
                        'definition' => T::tr('A set still factory-sealed in its original box, in pristine condition. Commands the highest resale prices.'),
                    ],
                    [
                        'term'       => 'NISB',
                        'full'       => 'New In Sealed Box',
                        'definition' => T::tr('A set that has never been opened. Often used interchangeably with MISB.'),
                    ],
                    [
                        'term'       => 'MIB',
                        'full'       => 'Mint In Box',
                        'definition' => T::tr('A complete set with original box, but the seal may be broken. Lower value than MISB/NISB.'),
                    ],
                    [
                        'term'       => 'LBR',
                        'full'       => 'LEGO Brand Retail',
                        'definition' => T::tr('Official LEGO stores (LEGO.com and physical brand stores) — the primary source for exclusives and GWPs.'),
                    ],
                    [
                        'term'       => 'Resale value',
                        'full'       => null,
                        'definition' => T::tr('The price a set commands on the secondary market after retiring from retail. Often used to track sets as investments.'),
                    ],
                    [
                        'term'       => 'Investment piece',
                        'full'       => null,
                        'definition' => T::tr('A set bought primarily because it’s expected to grow in value after retirement, rather than to build.'),
                    ],
                ],
            ],
            [
                'slug'  => 'tools',
                'name'  => T::tr('Tools & Ecosystem'),
                'icon'  => 'bi-puzzle-fill',
                'intro' => T::tr('Apps, databases, and acronyms from the wider LEGO ecosystem.'),
                'terms' => [
                    [
                        'term'       => 'LDraw',
                        'full'       => null,
                        'definition' => T::tr('An open standard and library of part files for digital LEGO models. The backbone of most third-party CAD tools.'),
                    ],
                    [
                        'term'       => 'Stud.io',
                        'full'       => null,
                        'definition' => T::tr('A free LEGO CAD program from BrickLink, with rendering, instructions, and an integrated parts ordering flow.'),
                    ],
                    [
                        'term'       => 'LDD',
                        'full'       => 'LEGO Digital Designer',
                        'definition' => T::tr('LEGO’s official (now discontinued) CAD program. Replaced in the community by Stud.io.'),
                    ],
                    [
                        'term'       => 'Rebrickable',
                        'full'       => null,
                        'definition' => T::tr('A community database of sets, parts, and minifigures, with alternate-build instructions for parts you already own.'),
                    ],
                    [
                        'term'       => 'Brickset',
                        'full'       => null,
                        'definition' => T::tr('A long-running set database with reviews, news, prices, and collection-tracking tools for AFOLs.'),
                    ],
                    [
                        'term'       => 'WIP',
                        'full'       => 'Work in Progress',
                        'definition' => T::tr('Tag used when sharing a MOC that is still being built or refined — feedback welcome.'),
                    ],
                    [
                        'term'       => 'Clone brand',
                        'full'       => null,
                        'definition' => T::tr('A competing brick brand (e.g. COBI, Mega Construx) producing LEGO-compatible elements. Generally not considered "LEGO".'),
                    ],
                ],
            ],
        ];
    }
}
