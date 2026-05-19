<?php

namespace frontend\modules\user\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\OwnedSet;
use common\models\Set;
use common\models\SetReview;
use common\models\User;
use common\models\Wishlist;
use frontend\components\T;
use Yii;
use yii\helpers\Json;

class ProfileController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index'],
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $userId = (int)$user->id;

        $ownedCount = (int)OwnedSet::find()->where(['user_id' => $userId])->count();
        $wishlistCount = (int)Wishlist::find()->where(['user_id' => $userId])->count();
        $reviewCount = (int)SetReview::find()->where(['user_id' => $userId])->count();

        $reviewProfile = SetReview::getUserReviewProfile($userId);
        $communityDimensions = SetReview::getCommunityDimensionAverages();
        $recommendedSets = $this->buildRecommendations($userId, (int)$reviewProfile['detailed_count']);

        $dimensionShortLabels = [
            'design'           => T::tr('Look'),
            'build_experience' => T::tr('Build'),
            'playability'      => T::tr('Features'),
            'quality'          => T::tr('Quality'),
            'value'            => T::tr('Value'),
            'recommendation'   => T::tr('Overall'),
        ];

        $radar = $this->buildRadarData(
            $reviewProfile['dimensions'] ?? [],
            $communityDimensions,
            $dimensionShortLabels
        );
        $takeaways = SetReview::summarizeDimensionDiffs($reviewProfile['dimension_diffs'] ?? []);

        return $this->render('index', [
            'user'                 => $user,
            'ownedCount'           => $ownedCount,
            'wishlistCount'        => $wishlistCount,
            'reviewCount'          => $reviewCount,
            'reviewProfile'        => $reviewProfile,
            'communityDimensions'  => $communityDimensions,
            'recommendedSets'      => $recommendedSets,
            'dimensionShortLabels' => $dimensionShortLabels,
            'radarChartJson'       => $radar['json'],
            'hasRadarData'         => $radar['hasData'],
            'takeawayStrictest'    => $takeaways['strictest'],
            'takeawayGenerous'     => $takeaways['mostGenerous'],
        ]);
    }

    /**
     * @return array<int, array{set: Set, peer_count: int}>
     */
    private function buildRecommendations(int $userId, int $detailedReviewCount): array
    {
        if ($detailedReviewCount < 1) {
            return [];
        }
        $picks = SetReview::getSimilarUsersRecommendations($userId, 6);
        if ($picks === []) {
            return [];
        }
        $setIds = array_column($picks, 'set_id');
        $sets = Set::find()->where(['id' => $setIds])->indexBy('id')->all();
        $result = [];
        foreach ($picks as $pick) {
            if (isset($sets[$pick['set_id']])) {
                $result[] = [
                    'set'        => $sets[$pick['set_id']],
                    'peer_count' => (int)$pick['peer_count'],
                ];
            }
        }
        return $result;
    }

    /**
     * @param array<string, array{avg: float, count: int}> $userDimensions
     * @param array<string, float>                         $communityDimensions
     * @param array<string, string>                        $labels
     * @return array{json: string, hasData: bool}
     */
    private function buildRadarData(array $userDimensions, array $communityDimensions, array $labels): array
    {
        $order = array_keys(SetReview::DIMENSIONS);
        $radarLabels = [];
        $userValues = [];
        $communityValues = [];
        foreach ($order as $dim) {
            $radarLabels[] = $labels[$dim] ?? $dim;
            $userValues[] = isset($userDimensions[$dim]['avg']) ? round((float)$userDimensions[$dim]['avg'], 2) : 0;
            $communityValues[] = isset($communityDimensions[$dim]) ? round((float)$communityDimensions[$dim], 2) : 0;
        }
        return [
            'json'    => Json::encode([
                'labels'         => $radarLabels,
                'values'         => $userValues,
                'community'      => $communityValues,
                'label'          => T::tr('You'),
                'communityLabel' => T::tr('Community avg'),
            ]),
            'hasData' => !empty($userDimensions) && !empty($communityDimensions),
        ];
    }
}
