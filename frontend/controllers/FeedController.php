<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use frontend\components\FeedService;
use Yii;
use yii\web\Response;

class FeedController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow'   => true,
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): Response
    {
        $payload = (new FeedService())->buildFeed();

        // Strip internal sort/debug fields before emitting.
        $payload['items'] = array_map(static function (array $item): array {
            unset($item['_kind']);
            return $item;
        }, $payload['items']);

        // Use FORMAT_RAW with manual encoding so the Content-Type below isn't
        // overwritten by Yii's JsonResponseFormatter (which forces application/json).
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/feed+json; charset=UTF-8');
        $response->headers->set('Cache-Control', 'public, max-age=' . FeedService::CACHE_TTL);
        $response->data = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return $response;
    }
}
