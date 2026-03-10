<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use common\enums\image\StatusEnum;
use common\models\Set;
use common\models\SetImage;
use common\models\SetOffer;
use common\models\Theme;

/**
 * Default controller for the `admin` module
 */
class DashboardController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $totalSets = (int)Set::find()->count();
        $activeSets = (int)Set::find()->where(['status' => StatusEnum::ACTIVE->value])->count();
        $inactiveSets = (int)Set::find()->where(['status' => StatusEnum::INACTIVE->value])->count();
        $totalThemes = (int)Theme::find()->count();
        $totalOffers = (int)SetOffer::find()->count();
        $totalImages = (int)SetImage::find()->count();
        $setsWithPrice = (int)Set::find()->where(['not', ['price' => null]])->count();
        $setsWithoutImages = (int)Set::find()
            ->alias('set')
            ->leftJoin(['set_image' => SetImage::tableName()], 'set_image.set_id = set.id')
            ->where(['set_image.id' => null])
            ->count();

        $recentSets = Set::find()
            ->with(['theme'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(8)
            ->all();

        $topThemes = Theme::find()
            ->where(['parent_id' => null])
            ->orderBy(['sets_count' => SORT_DESC, 'name' => SORT_ASC])
            ->limit(6)
            ->all();

        return $this->render('index', [
            'stats' => [
                [
                    'label' => 'Total sets',
                    'value' => $totalSets,
                    'hint' => 'All catalog entries',
                ],
                [
                    'label' => 'Active sets',
                    'value' => $activeSets,
                    'hint' => 'Visible in catalog',
                ],
                [
                    'label' => 'Inactive sets',
                    'value' => $inactiveSets,
                    'hint' => 'Require review',
                ],
                [
                    'label' => 'Themes',
                    'value' => $totalThemes,
                    'hint' => 'Main and subthemes',
                ],
                [
                    'label' => 'Offers',
                    'value' => $totalOffers,
                    'hint' => 'Imported store offers',
                ],
                [
                    'label' => 'Images',
                    'value' => $totalImages,
                    'hint' => 'Stored set images',
                ],
                [
                    'label' => 'Sets with price',
                    'value' => $setsWithPrice,
                    'hint' => 'Base price available',
                ],
                [
                    'label' => 'Sets without images',
                    'value' => $setsWithoutImages,
                    'hint' => 'Need asset completion',
                ],
            ],
            'recentSets' => $recentSets,
            'topThemes' => $topThemes,
        ]);
    }
}
