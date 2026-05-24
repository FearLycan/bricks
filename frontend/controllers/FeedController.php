<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use frontend\components\AtomFeedRenderer;
use frontend\components\FeedService;
use Yii;
use yii\helpers\Url;
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
                        'actions' => ['index', 'xml'],
                        'allow'   => true,
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): Response
    {
        $payload = $this->buildPublicPayload();

        return $this->emitRaw(
            'application/feed+json; charset=UTF-8',
            json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            ),
        );
    }

    public function actionXml(): Response
    {
        $payload = $this->buildPublicPayload();
        // The Atom feed_url differs from the JSON one — point self-link at the right file.
        $payload['feed_url'] = Url::to(['/feed.xml'], true);

        return $this->emitRaw(
            'application/atom+xml; charset=UTF-8',
            (new AtomFeedRenderer())->render($payload),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPublicPayload(): array
    {
        $payload = (new FeedService())->buildFeed();

        $payload['items'] = array_map(static function (array $item): array {
            unset($item['_kind']);
            return $item;
        }, $payload['items']);

        return $payload;
    }

    private function emitRaw(string $contentType, string $body): Response
    {
        // Use FORMAT_RAW with manual encoding so the Content-Type below isn't
        // overwritten by Yii's response formatters.
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Cache-Control', 'public, max-age=' . FeedService::CACHE_TTL);
        $response->data = $body;

        return $response;
    }
}
