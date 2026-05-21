<?php

namespace frontend\modules\homepage\services;

use common\enums\StatusEnum;
use common\models\OwnedSet;
use common\models\Set;
use common\models\SetMinifig;
use common\models\Theme;
use common\models\User;
use common\models\Wishlist;
use frontend\components\T;
use Yii;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * Source of truth for the homepage content.
 *
 * Each public method returns a section payload. Controllers and views must
 * not query the database directly — they consume what this service provides.
 * See frontend/modules/homepage/PLAN.md.
 */
class HomepageContentService
{
    /**
     * Cache TTLs in seconds. Read from Yii::$app->params with sane fallbacks.
     * Set any to 0 in params-local.php to bypass cache (e.g. for local dev).
     */
    private int $cacheTtlCatalog;
    private int $cacheTtlOnSale;
    private int $cacheTtlBranding;
    private int $cacheTtlPersonal;

    public function __construct()
    {
        $params = Yii::$app->params;

        $this->cacheTtlCatalog = (int)($params['homepage.cache.catalog'] ?? 600);
        $this->cacheTtlOnSale = (int)($params['homepage.cache.onSale'] ?? 300);
        $this->cacheTtlBranding = (int)($params['homepage.cache.branding'] ?? 1800);
        $this->cacheTtlPersonal = (int)($params['homepage.cache.personal'] ?? 60);
    }

    /**
     * Cache wrapper that bypasses the cache when TTL <= 0 (useful for local dev).
     *
     * @template T
     * @param callable():T $producer
     * @return T
     */
    private function cached(string $key, callable $producer, int $ttl)
    {
        if ($ttl <= 0) {
            return $producer();
        }

        return Yii::$app->cache->getOrSet($key, $producer, $ttl);
    }

    /**
     * @return Set[]
     */
    public function getHeroSlides(int $limit = 5): array
    {
        return $this->cached("homepage.hero.{$limit}", function () use ($limit) {
            return Set::find()
                ->alias('s')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['not', ['s.rating' => null]])
                ->andWhere(['>=', 's.rating', 4.3])
                ->andWhere(['or',
                            ['s.launch_date' => null],
                            ['<=', 's.launch_date', new Expression('CURDATE()')],
                ])
                ->with(['mainImageRelation', 'images', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(new Expression('s.launch_date IS NULL ASC, s.launch_date DESC, s.rating DESC, s.id DESC'))
                ->limit($limit)
                ->all();
        }, $this->cacheTtlBranding);
    }

    /**
     * Returns the structure for the three browse tabs (New / Themes / For who).
     * Each tab has a translated label and a list of tile DTOs:
     *   ['label' => string, 'url' => string, 'image' => string, 'accent' => ?string]
     *
     * @return array<string, array{label: string, tiles: array<int, array{label: string, url: string, image: string, accent: ?string}>}>
     */
    public function getBrowseTabs(): array
    {
        $themes = $this->getThemeTiles();

        return [
            'new'      => [
                'label' => T::tr('New'),
                'tiles' => $this->buildNewTabTiles($themes),
            ],
            /*'themes'   => [
                'label' => T::tr('Themes'),
                'tiles' => $this->themesToTiles($themes),
            ],*/
            'audience' => [
                'label' => T::tr('For who'),
                'tiles' => $this->buildAudienceTabTiles(),
            ],
        ];
    }

    /**
     * @param Theme[] $themes
     * @return array<int, array{label: string, url: string, image: string, accent: ?string}>
     */
    private function buildNewTabTiles(array $themes): array
    {
        $tiles = [
            $this->makeTile(T::tr('All new arrivals'), ['/lego'], 'gradient-blue', 'images/browse/new-arrivals.jpg'),
            $this->makeTile(T::tr('On sale'), ['/lego/on-sale'], 'gradient-red', 'images/browse/on-sale.jpg'),
            $this->makeTile(T::tr('LEGO® for adults'), ['/lego', 'age_min' => 18], 'gradient-purple', 'images/browse/adults.jpg'),
            $this->makeTile(T::tr('Browse all sets'), ['/lego'], 'gradient-amber', 'images/browse/all-sets.jpg'),
        ];

        foreach (array_slice($themes, 0, 4) as $theme) {
            $tiles[] = $this->themeToTile($theme);
        }

        return $tiles;
    }

    /**
     * @return array<int, array{label: string, url: string, image: string, accent: ?string}>
     */
    private function buildAudienceTabTiles(): array
    {
        return [
            $this->makeTile(T::tr('For toddlers (1+)'), ['/lego', 'age_max' => 4], 'gradient-pink', 'images/browse/toddlers.jpg'),
            $this->makeTile(T::tr('For kids (5–8)'), ['/lego', 'age_min' => 5, 'age_max' => 8], 'gradient-amber', 'images/browse/kids.jpg'),
            $this->makeTile(T::tr('For tweens (9–12)'), ['/lego', 'age_min' => 9, 'age_max' => 12], 'gradient-green', 'images/browse/tweens.jpg'),
            $this->makeTile(T::tr('For teens (13+)'), ['/lego', 'age_min' => 13], 'gradient-blue', 'images/browse/teens.jpg'),
            $this->makeTile(T::tr('For adults (18+)'), ['/lego', 'age_min' => 18], 'gradient-purple', 'images/browse/adults.jpg'),
            $this->makeTile(T::tr('Small builds (≤200 pcs)'), ['/lego', 'pieces_max' => 200], 'gradient-teal', 'images/browse/small-builds.jpg'),
            $this->makeTile(T::tr('Big builds (2000+ pcs)'), ['/lego', 'pieces_min' => 2000], 'gradient-red', 'images/browse/big-builds.jpg'),
            $this->makeTile(T::tr('Browse all sets'), ['/lego'], 'gradient-slate', 'images/browse/all-sets.jpg'),
        ];
    }

    /**
     * @param Theme[] $themes
     * @return array<int, array{label: string, url: string, image: string, accent: ?string}>
     */
    private function themesToTiles(array $themes): array
    {
        return array_map(fn(Theme $t) => $this->themeToTile($t), $themes);
    }

    /**
     * @return array{label: string, url: string, image: string, accent: ?string}
     */
    private function themeToTile(Theme $theme): array
    {
        $name = (string)$theme->name;
        $image = trim((string)$theme->image);

        return [
            'label'  => $name,
            'url'    => Url::to("/lego/theme/{$theme->slug}"),
            'image'  => $image !== '' ? $image : $this->placeholderImage($name),
            'accent' => null,
        ];
    }

    /**
     * @param array|string $route
     * @param string|null  $image Tile image: an absolute URL or a path relative
     *                            to the web root (e.g. 'images/browse/teens.jpg').
     *                            Falls back to a generated placeholder when null
     *                            or when a local file is missing.
     * @return array{label: string, url: string, image: string, accent: ?string}
     */
    private function makeTile(string $label, $route, ?string $accent = null, ?string $image = null): array
    {
        return [
            'label'  => $label,
            'url'    => Url::to($route),
            'image'  => $this->resolveImage($image, $label),
            'accent' => $accent,
        ];
    }

    /**
     * Resolves a tile image link. A null value, or a relative path pointing to a
     * file that does not exist on disk, falls back to a generated placeholder so
     * the homepage never renders a broken image.
     */
    private function resolveImage(?string $image, string $label): string
    {
        $image = $image !== null ? trim($image) : '';
        if ($image === '') {
            return $this->placeholderImage($label);
        }

        if (preg_match('#^(https?:)?//#', $image)) {
            return $image;
        }

        $relative = ltrim($image, '/');
        if (!is_file(Yii::getAlias('@webroot') . '/' . $relative)) {
            return $this->placeholderImage($label);
        }

        return Yii::getAlias('@web') . '/' . $relative;
    }

    private function placeholderImage(string $label): string
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s+]/u', ' ', $label);
        $clean = trim((string)preg_replace('/\s+/', ' ', (string)$clean));
        if ($clean === '') {
            $clean = 'LEGO';
        }

        return 'https://placehold.co/600x800/0d2545/ffffff?text=' . rawurlencode($clean) . '&font=montserrat';
    }

    /**
     * @return Theme[]
     */
    public function getThemeTiles(int $limit = 12): array
    {
        return $this->cached("homepage.themeTiles.{$limit}", function () use ($limit) {
            return Theme::find()
                ->alias('t')
                ->andWhere(['t.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['t.parent_id' => null])
                ->andWhere(['>', 't.sets_count', 0])
                ->orderBy(['t.sets_count' => SORT_DESC, 't.name' => SORT_ASC])
                ->limit($limit)
                ->all();
        }, $this->cacheTtlBranding);
    }

    /**
     * @return Set[]
     */
    public function getNewArrivals(int $limit = 12): array
    {
        return $this->cached("homepage.newArrivals.{$limit}", function () use ($limit) {
            return Set::find()
                ->alias('s')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['not', ['s.launch_date' => null]])
                ->andWhere(['<=', 's.launch_date', new Expression('CURDATE()')])
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(['s.launch_date' => SORT_DESC, 's.id' => SORT_DESC])
                ->limit($limit)
                ->all();
        }, $this->cacheTtlCatalog);
    }

    /**
     * @return Set[]
     */
    public function getOnSale(int $limit = 12): array
    {
        return $this->cached("homepage.onSale.{$limit}", function () use ($limit) {
            $offerSubquery = '(SELECT set_id, MIN(price) AS min_price FROM {{%set_offer}}'
                . ' WHERE currency_code = \'USD\' AND price > 0 GROUP BY set_id)';

            return Set::find()
                ->alias('s')
                ->select(['s.*'])
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['not', ['s.price' => null]])
                ->andWhere(['>', 's.price', 0])
                ->innerJoin($offerSubquery . ' so', 'so.set_id = s.id AND so.min_price < s.price')
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(new Expression('(s.price - so.min_price) / s.price DESC'))
                ->limit($limit)
                ->all();
        }, $this->cacheTtlOnSale);
    }

    /**
     * @return Set[]
     */
    public function getTopRated(int $limit = 12): array
    {
        return $this->cached("homepage.topRated.{$limit}", function () use ($limit) {
            return Set::find()
                ->alias('s')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['not', ['s.rating' => null]])
                ->andWhere(['>=', 's.rating', 4.0])
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(['s.rating' => SORT_DESC, 's.id' => SORT_DESC])
                ->limit($limit)
                ->all();
        }, $this->cacheTtlCatalog);
    }

    /**
     * @return Set[]
     */
    public function getComingSoon(int $limit = 12): array
    {
        return $this->cached("homepage.comingSoon.{$limit}", function () use ($limit) {
            return Set::find()
                ->alias('s')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['>', 's.launch_date', new Expression('CURDATE()')])
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(['s.launch_date' => SORT_ASC, 's.id' => SORT_ASC])
                ->limit($limit)
                ->all();
        }, $this->cacheTtlCatalog);
    }

    /**
     * @return Set[]
     */
    public function getForAdults(int $limit = 12): array
    {
        return $this->cached("homepage.forAdults.{$limit}", function () use ($limit) {
            return Set::find()
                ->alias('s')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['>=', 's.age', 18])
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(new Expression('s.rating IS NULL ASC, s.rating DESC, s.id DESC'))
                ->limit($limit)
                ->all();
        }, $this->cacheTtlCatalog);
    }

    public function getThemeSpotlight(): ?Theme
    {
        $dayBucket = date('Y-m-d');
        return $this->cached("homepage.themeSpotlight.{$dayBucket}", function () {
            $candidates = Theme::find()
                ->alias('t')
                ->andWhere(['t.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['t.parent_id' => null])
                ->andWhere(['>=', 't.sets_count', 5])
                ->orderBy(['t.sets_count' => SORT_DESC])
                ->limit(8)
                ->all();

            if (empty($candidates)) {
                return null;
            }

            $index = (int)date('z') % count($candidates);

            return $candidates[$index];
        }, $this->cacheTtlBranding);
    }

    /**
     * @return SetMinifig[]
     */
    public function getFeaturedMinifigs(int $limit = 8): array
    {
        return $this->cached("homepage.featuredMinifigs.{$limit}", function () use ($limit) {
            $candidates = SetMinifig::find()
                ->alias('sm')
                ->select(['sm.*'])
                ->innerJoin(['s' => Set::tableName()], 's.id = sm.set_id')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['not', ['sm.image' => null]])
                ->andWhere(['<>', 'sm.image', ''])
                ->andWhere(['or',
                            ['s.launch_date' => null],
                            ['<=', 's.launch_date', new Expression('CURDATE()')],
                ])
                ->orderBy(['sm.created_at' => SORT_DESC, 'sm.id' => SORT_DESC])
                ->limit($limit * 6)
                ->all();

            $seen = [];
            $picked = [];
            foreach ($candidates as $minifig) {
                $key = (string)$minifig->number;
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $picked[] = $minifig;
                if (count($picked) >= $limit) {
                    break;
                }
            }

            return $picked;
        }, $this->cacheTtlBranding);
    }

    /**
     * @return Set[]
     */
    public function getPersonalWishlistPreview(User $user, int $limit = 4): array
    {
        $userId = (int)$user->id;

        return $this->cached("homepage.personal.{$userId}.wishlist.{$limit}", function () use ($userId, $limit) {
            return Set::find()
                ->alias('s')
                ->innerJoin(['w' => Wishlist::tableName()], 'w.set_id = s.id')
                ->andWhere(['w.user_id' => $userId])
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(['w.created_at' => SORT_DESC, 'w.id' => SORT_DESC])
                ->limit($limit)
                ->all();
        }, $this->cacheTtlPersonal);
    }

    /**
     * Sets recommended based on themes present in the user's collection and wishlist,
     * excluding sets the user already owns or has on their wishlist.
     *
     * Fallback: top-rated sets globally when the user has no behavioral data yet.
     *
     * @return Set[]
     */
    public function getPersonalRecommendations(User $user, int $limit = 12): array
    {
        $userId = (int)$user->id;

        return $this->cached("homepage.personal.{$userId}.recommendations.{$limit}", function () use ($userId, $limit) {
            $themeIds = (new \yii\db\Query())
                ->select(['s.theme_id'])
                ->from(['s' => Set::tableName()])
                ->innerJoin(['o' => OwnedSet::tableName()], 'o.set_id = s.id')
                ->where(['o.user_id' => $userId])
                ->andWhere(['not', ['s.theme_id' => null]])
                ->union(
                    (new \yii\db\Query())
                        ->select(['s2.theme_id'])
                        ->from(['s2' => Set::tableName()])
                        ->innerJoin(['w' => Wishlist::tableName()], 'w.set_id = s2.id')
                        ->where(['w.user_id' => $userId])
                        ->andWhere(['not', ['s2.theme_id' => null]])
                )
                ->column();

            $themeIds = array_values(array_unique(array_map('intval', $themeIds)));

            $excludedSetIds = (new \yii\db\Query())
                ->select(['o.set_id'])
                ->from(['o' => OwnedSet::tableName()])
                ->where(['o.user_id' => $userId])
                ->union(
                    (new \yii\db\Query())
                        ->select(['w.set_id'])
                        ->from(['w' => Wishlist::tableName()])
                        ->where(['w.user_id' => $userId])
                )
                ->column();
            $excludedSetIds = array_values(array_unique(array_map('intval', $excludedSetIds)));

            if (empty($themeIds)) {
                return $this->getTopRated($limit);
            }

            $query = Set::find()
                ->alias('s')
                ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
                ->andWhere(['s.theme_id' => $themeIds])
                ->with(['mainImageRelation', 'theme', 'subtheme', 'setOffers'])
                ->orderBy(new Expression('s.rating IS NULL ASC, s.rating DESC, s.launch_date DESC, s.id DESC'))
                ->limit($limit);

            if (!empty($excludedSetIds)) {
                $query->andWhere(['not in', 's.id', $excludedSetIds]);
            }

            $items = $query->all();

            if (empty($items)) {
                return $this->getTopRated($limit);
            }

            return $items;
        }, $this->cacheTtlPersonal);
    }

    /**
     * @return array{sets_owned:int, pieces_total:int, top_theme:?Theme, wishlist_size:int}
     */
    public function getPersonalCollectionStats(User $user): array
    {
        $userId = (int)$user->id;

        return $this->cached("homepage.personal.{$userId}.stats", function () use ($userId): array {
            $ownedCount = (int)OwnedSet::find()
                ->alias('o')
                ->where(['o.user_id' => $userId])
                ->count('o.id');

            $piecesTotal = (int)Set::find()
                ->alias('s')
                ->innerJoin(['o' => OwnedSet::tableName()], 'o.set_id = s.id')
                ->where(['o.user_id' => $userId])
                ->andWhere(['not', ['s.pieces' => null]])
                ->sum('s.pieces');

            $wishlistSize = (int)Wishlist::find()
                ->alias('w')
                ->where(['w.user_id' => $userId])
                ->count('w.id');

            $topThemeId = (new \yii\db\Query())
                ->select(['s.theme_id'])
                ->from(['s' => Set::tableName()])
                ->innerJoin(['o' => OwnedSet::tableName()], 'o.set_id = s.id')
                ->where(['o.user_id' => $userId])
                ->andWhere(['not', ['s.theme_id' => null]])
                ->groupBy(['s.theme_id'])
                ->orderBy(new Expression('COUNT(*) DESC'))
                ->limit(1)
                ->scalar();

            $topTheme = null;
            if ($topThemeId !== false && $topThemeId !== null) {
                $topTheme = Theme::findOne((int)$topThemeId);
            }

            return [
                'sets_owned'    => $ownedCount,
                'pieces_total'  => $piecesTotal,
                'top_theme'     => $topTheme,
                'wishlist_size' => $wishlistSize,
            ];
        }, $this->cacheTtlPersonal);
    }
}
