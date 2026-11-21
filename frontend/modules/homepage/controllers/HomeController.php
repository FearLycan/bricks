<?php

namespace frontend\modules\homepage\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\User;
use frontend\modules\homepage\services\HomepageContentService;
use Yii;

class HomeController extends Controller
{
    private HomepageContentService $content;

    public function __construct($id, $module, $config = [])
    {
        $this->content = new HomepageContentService();
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index'],
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $themeTiles = $this->content->getThemeTiles();
        $themeSpotlight = $this->content->getThemeSpotlight();
        $heroSlides = $this->content->getHeroSlides();
        // Hero and "New arrivals" both pick recent sets: keep a set in one place only.
        $heroIds = array_map(static fn($s) => (int)$s->id, $heroSlides);
        $newArrivals = array_slice(array_values(array_filter(
            $this->content->getNewArrivals(12 + count($heroIds)),
            static fn($s) => !in_array((int)$s->id, $heroIds, true)
        )), 0, 12);
        $viewData = [
            'heroSlides'        => $heroSlides,
            'themeTiles'        => $themeTiles,
            'themeCovers'       => $this->content->getThemeCovers($themeTiles),
            'ageTiles'          => $this->content->getAgeTiles(),
            'newArrivals'       => $newArrivals,
            'onSale'            => $this->content->getOnSale(),
            'topRated'          => $this->content->getTopRated(),
            'comingSoon'        => $this->content->getComingSoon(),
            'forAdults'         => $this->content->getForAdults(),
            'themeSpotlight'    => $themeSpotlight,
            'spotlightSets'     => $themeSpotlight ? $this->content->getThemeSpotlightSets($themeSpotlight) : ['picks' => [], 'news' => []],
            'seasonalSpotlight' => $this->content->getSeasonalSpotlight(),
            'featuredMinifigs'  => $this->content->getFeaturedMinifigs(),
            'wishlistPreview'   => [],
            'recommendations'   => [],
            'collectionStats'   => null,
        ];

        $user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
        if ($user instanceof User) {
            $viewData['wishlistPreview'] = $this->content->getPersonalWishlistPreview($user);
            $viewData['recommendations'] = $this->content->getPersonalRecommendations($user);
            $viewData['collectionStats'] = $this->content->getPersonalCollectionStats($user);
        }

        return $this->render('index', $viewData);
    }
}
