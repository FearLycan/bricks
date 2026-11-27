<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\enums\StatusEnum;
use common\models\Set;
use common\models\Theme;
use frontend\components\GiftGuide;
use frontend\components\AtomFeedRenderer;
use frontend\components\FeedService;
use frontend\components\Rss2FeedRenderer;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
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
                        'actions' => ['index', 'xml', 'rss', 'pinterest'],
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

    public function actionRss(): Response
    {
        $payload = $this->buildPublicPayload();
        // RSS self-link must point at the RSS endpoint, not the Atom/JSON ones.
        $payload['feed_url'] = Url::to(['/feed.rss'], true);

        return $this->emitRaw(
            'application/rss+xml; charset=UTF-8',
            (new Rss2FeedRenderer())->render($payload),
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

    /**
     * RSS feeds for Pinterest auto-publishing, one per board (ported from store-base):
     *
     *   /pinterest/all.rss              every set people can buy
     *   /pinterest/<theme-slug>.rss     one theme (star-wars, technic, ...), MIN_THEME_SETS or more
     *   /pinterest/<gift-guide>.rss     the sets of one gift guide (gifts-for-adults, ...)
     *
     * Pinterest reads a feed about once a day and pins items it has not seen. Each feed shows
     * a daily window of `pinterest.perDay` sets that walks through the whole list, newest and
     * biggest first, so a board keeps getting new pins and the catalogue gets pinned over time.
     * English only (pins link to the English pages).
     */
    public function actionPinterest(string $slug): Response
    {
        Yii::$app->language = 'en';
        [$title, $sets] = $this->pinterestSource($slug);
        $perDay = max(1, (int)(Yii::$app->params['pinterest.perDay'] ?? 10));
        $day = intdiv(time(), 86400);

        $xml = Yii::$app->cache->getOrSet(['pinterest-feed', $slug, $day, $perDay, 1], function () use ($title, $sets, $perDay, $day, $slug): string {
            $total = count($sets);
            $offset = $total > 0 ? ($day * $perDay) % $total : 0;
            $items = array_slice($sets, $offset, $perDay);
            if (count($items) < $perDay && $total > $perDay) {
                $items = array_merge($items, array_slice($sets, 0, $perDay - count($items)));
            }

            return $this->renderPartial('pinterest', ['title' => $title, 'slug' => $slug, 'items' => $items, 'date' => $day * 86400]);
        }, 3600);

        $response = $this->emitRaw('application/rss+xml; charset=UTF-8', $xml);
        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }

    /** Themes need this many sets for a board of their own. */
    public const MIN_THEME_SETS = 20;

    /** @return array{0: string, 1: Set[]} */
    private function pinterestSource(string $slug): array
    {
        if ($slug === 'all') {
            return ['LEGO sets | BrickAtlas', self::pinnable(Set::find())];
        }
        if (isset(GiftGuide::SLUGS[$slug]) && ($guide = GiftGuide::get(GiftGuide::SLUGS[$slug])) !== null) {
            $numbers = [];
            foreach ($guide['sections'] as $section) {
                $numbers = array_merge($numbers, array_map('strval', array_keys($section['sets'])));
            }
            $ids = array_map(static fn(Set $set): int => (int)$set->id, GiftGuide::loadSets($numbers));

            return [$guide['title'] . ' | BrickAtlas', self::pinnable(Set::find()->andWhere(['s.id' => $ids ?: [0]]))];
        }
        $theme = Theme::find()->where(['slug' => $slug, 'parent_id' => null])->one();
        if ($theme !== null) {
            $sets = self::pinnable(Set::find()->andWhere(['s.theme_id' => $theme->id]));
            if (count($sets) >= self::MIN_THEME_SETS) {
                return ['LEGO ' . $theme->name . ' sets | BrickAtlas', $sets];
            }
        }

        throw new NotFoundHttpException('Unknown feed.');
    }

    /**
     * Released sets on sale with a main image, real sets only (no gear, polybags or freebies),
     * newest and biggest first.
     *
     * @return Set[]
     */
    private static function pinnable(ActiveQuery $query): array
    {
        $today = date('Y-m-d');

        return $query->alias('s')
            ->with(['mainImageRelation', 'theme'])
            ->innerJoin(['i' => '{{%set_image}}'], "i.set_id = s.id AND i.kind = 'main'")
            ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['>=', 's.pieces', 30])
            ->andWhere(['or', ['s.availability' => null], ['not', ['s.availability' => ['Magazine gift', 'Promotional', 'LEGO Gift with Purchase', 'Insiders Reward']]]])
            ->andWhere(['or', ['s.launch_date' => null], ['<=', 's.launch_date', $today]])
            ->andWhere(['or', ['s.exit_date' => null], ['>=', 's.exit_date', $today]])
            ->groupBy('s.id')
            ->orderBy(['s.year' => SORT_DESC, 's.pieces' => SORT_DESC, 's.id' => SORT_ASC])
            ->limit(3000)
            ->all();
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
