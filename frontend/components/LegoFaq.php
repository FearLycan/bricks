<?php

namespace frontend\components;

/**
 * Source of truth for the public FAQ at /faq.
 *
 * Adding a question: append one row to a category's `items` array. The view
 * builds anchors from the category slug and renders one Bootstrap accordion
 * per category — no extra config needed.
 *
 * Item schema:
 *   - q: the question (short, written as the user would phrase it)
 *   - a: full answer; may contain plain text. Markup is escaped in the view.
 */
final class LegoFaq
{
    /**
     * @return array<int, array{slug:string,name:string,icon:string,items:array<int,array{q:string,a:string}>}>
     */
    public static function getCategories(): array
    {
        return [
            [
                'slug' => 'about',
                'name' => T::tr('About BrickAtlas'),
                'icon' => 'bi-info-circle-fill',
                'items' => [
                    [
                        'q' => T::tr('What is BrickAtlas?'),
                        'a' => T::tr('BrickAtlas is a price tracker and discovery tool for LEGO® fans. We pull data from the LEGO catalog, match it against current store offers, and help you find deals, track sets, and explore the catalog by theme, interest or budget.'),
                    ],
                    [
                        'q' => T::tr('Are you affiliated with The LEGO Group?'),
                        'a' => T::tr('No. BrickAtlas is an independent project, not sponsored, authorized or endorsed by The LEGO Group. LEGO® is a trademark of the LEGO Group.'),
                    ],
                    [
                        'q' => T::tr('Is BrickAtlas free to use?'),
                        'a' => T::tr('Yes. The catalog, prices, wishlist and ratings are all free. We may earn a commission from some store links (see Data and privacy).'),
                    ],
                    [
                        'q' => T::tr('Which languages does BrickAtlas support?'),
                        'a' => T::tr('The interface is available in English, Polish, German, French, Spanish, Italian, Japanese and Chinese. You can switch languages from the flag selector in the top bar.'),
                    ],
                ],
            ],
            [
                'slug' => 'prices',
                'name' => T::tr('Prices and offers'),
                'icon' => 'bi-tags-fill',
                'items' => [
                    [
                        'q' => T::tr('Where do you get prices from?'),
                        'a' => T::tr('The catalog price comes from LEGO itself. Offers from other stores are pulled from the stores we track and refreshed regularly. Every offer card links straight to the store.'),
                    ],
                    [
                        'q' => T::tr('How often are prices updated?'),
                        'a' => T::tr('Most offers are refreshed at least once a day. Highly volatile stores (marketplaces, flash sales) are checked more often. The catalog price from LEGO is updated whenever it changes.'),
                    ],
                    [
                        'q' => T::tr('Why does the price in a store differ from what BrickAtlas shows?'),
                        'a' => T::tr('Stores adjust prices between our refreshes, run hidden coupons or charge regional pricing. The link on every offer takes you straight to the live listing, so you can verify the current price before buying.'),
                    ],
                    [
                        'q' => T::tr('What does "On Sale" mean?'),
                        'a' => T::tr('"On Sale" lists sets where the lowest store offer we found is below the official LEGO price. The bigger the gap, the higher the saving — sorted from the biggest discount.'),
                    ],
                    [
                        'q' => T::tr('What is the "Best offer" label on a set page?'),
                        'a' => T::tr('"Best offer" is the cheapest store offer we currently track for that set in USD. We pick the lowest price, then break ties using store rating and review count.'),
                    ],
                    [
                        'q' => T::tr('Do offer prices include shipping?'),
                        'a' => T::tr('No — the price shown is the item price as listed by the store. Shipping, taxes and any coupon are calculated at the store checkout. Always verify the final price before placing the order.'),
                    ],
                ],
            ],
            [
                'slug' => 'account',
                'name' => T::tr('Account and access'),
                'icon' => 'bi-person-circle',
                'items' => [
                    [
                        'q' => T::tr('How do I create an account?'),
                        'a' => T::tr('Click "Register" in the top bar, fill in your username, email and password, and submit. We send a verification email with a link valid for 24 hours — click it to activate the account.'),
                    ],
                    [
                        'q' => T::tr('I didn\'t receive the verification email — what now?'),
                        'a' => T::tr('Check the spam folder first. If it\'s not there, use the "Resend verification" link on the sign-in page — we\'ll send a fresh link to the same address.'),
                    ],
                    [
                        'q' => T::tr('How do I reset my password?'),
                        'a' => T::tr('On the sign-in page click "Forgot password?", enter the email you registered with, and we\'ll send a reset link. The link expires after a short window for security reasons.'),
                    ],
                    [
                        'q' => T::tr('How do I change the interface language?'),
                        'a' => T::tr('Use the flag selector in the top bar to switch languages on any page. Signed-in users can also save a preferred language in Settings — we remember it across sessions and devices.'),
                    ],
                ],
            ],
            [
                'slug' => 'collection',
                'name' => T::tr('Wishlist and collection'),
                'icon' => 'bi-heart-fill',
                'items' => [
                    [
                        'q' => T::tr('How do I add a set to my wishlist?'),
                        'a' => T::tr('Hover any set card and tap the heart icon, or open the set page and use the heart button. You need to be signed in. The full list lives in "My wishlist" in the user menu.'),
                    ],
                    [
                        'q' => T::tr('How do I mark a set as owned?'),
                        'a' => T::tr('Tap the box icon on any set card or on the set page. Owned sets are saved to "My owned sets" and feed your collection stats on the homepage.'),
                    ],
                    [
                        'q' => T::tr('Can I hide sets I already own from the catalog?'),
                        'a' => T::tr('Yes. In Settings turn on "Hide sets I already own" — sets you marked as owned will disappear from the main catalog, search and new-arrival listings.'),
                    ],
                    [
                        'q' => T::tr('Is my wishlist visible to other users?'),
                        'a' => T::tr('No — your wishlist and owned sets are private. We use them only for your personal homepage and recommendations.'),
                    ],
                ],
            ],
            [
                'slug' => 'reviews',
                'name' => T::tr('Reviews and ratings'),
                'icon' => 'bi-star-fill',
                'items' => [
                    [
                        'q' => T::tr('How do I review a set?'),
                        'a' => T::tr('Open the set page, scroll to "Reviews" and click "Rate this set". Choose Quick rating (one score + optional note) or Detailed review (6 dimensions). You can switch between modes any time.'),
                    ],
                    [
                        'q' => T::tr('What\'s the difference between Quick and Detailed reviews?'),
                        'a' => T::tr('Quick rating is one overall score (0–10) plus an optional one-line note — 30 seconds tops. Detailed reviews score 6 dimensions (Look, Build, Features, Quality, Value, Overall) and power the radar chart on the set page.'),
                    ],
                    [
                        'q' => T::tr('What is my reviewer profile?'),
                        'a' => T::tr('After a few detailed reviews we build a profile that shows what you tend to value (display vs play, minifigures vs piece count, etc.) and whether you rate higher or lower than the community on each dimension.'),
                    ],
                    [
                        'q' => T::tr('Can I edit or delete my review?'),
                        'a' => T::tr('Yes. Open the set page or go to "My reviews" in your profile. Each review has Edit and Delete actions.'),
                    ],
                ],
            ],
            [
                'slug' => 'privacy',
                'name' => T::tr('Data and privacy'),
                'icon' => 'bi-shield-lock-fill',
                'items' => [
                    [
                        'q' => T::tr('What data do you collect about me?'),
                        'a' => T::tr('Only what we need to run the service: your username, email, hashed password, language preference, and the sets you save to your wishlist, owned list or reviews. We don\'t sell or share this data.'),
                    ],
                    [
                        'q' => T::tr('What are affiliate links?'),
                        'a' => T::tr('Some store links on offer cards are affiliate links — when you buy through them we may earn a small commission, at no extra cost to you. This is how we keep BrickAtlas free.'),
                    ],
                    [
                        'q' => T::tr('Where does the set data come from?'),
                        'a' => T::tr('Set, parts, minifigure and theme data come from the LEGO catalog and community databases (notably Rebrickable). We refresh regularly so new releases show up quickly.'),
                    ],
                    [
                        'q' => T::tr('Do you use cookies?'),
                        'a' => T::tr('Yes — we use cookies for sign-in sessions, language preference and basic analytics (Google Analytics). We don\'t use third-party advertising cookies.'),
                    ],
                ],
            ],
        ];
    }
}
