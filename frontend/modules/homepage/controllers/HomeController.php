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
        $viewData = [
            'heroSlides'       => $this->content->getHeroSlides(),
            'themeTiles'       => $this->content->getThemeTiles(),
            'browseTabs'       => $this->content->getBrowseTabs(),
            'newArrivals'      => $this->content->getNewArrivals(),
            'onSale'           => $this->content->getOnSale(),
            'topRated'         => $this->content->getTopRated(),
            'comingSoon'       => $this->content->getComingSoon(),
            'forAdults'        => $this->content->getForAdults(),
            'themeSpotlight'   => $this->content->getThemeSpotlight(),
            'featuredMinifigs' => $this->content->getFeaturedMinifigs(),
            'wishlistPreview'  => [],
            'recommendations'  => [],
            'collectionStats'  => null,
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
