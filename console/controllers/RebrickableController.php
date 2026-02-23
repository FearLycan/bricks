<?php

namespace console\controllers;

use common\models\Set;
use common\models\SetMinifig;
use Yii;
use yii\caching\CacheInterface;
use yii\console\Controller;
use yii\console\Exception;
use yii\httpclient\Client;

class RebrickableController extends Controller
{
    public Client         $client;
    public CacheInterface $cache;

    public function __construct($id, $module, $config = [])
    {
        $this->client = new Client(['baseUrl' => 'https://rebrickable.com/api/v3']);
        $this->cache = Yii::$app->cache;
        parent::__construct($id, $module, $config);
    }

    public function actionSyncMinifigs(?int $setNumber = null): void
    {
        $sets = Set::find()->where('minifigures > 0');

        if ($setNumber !== null) {
            $sets->andWhere(['number' => $setNumber]);
        }

        /** @var Set $set */
        foreach ($sets->each() as $set) {
            $response = $this->sendRequest("lego/sets/{$set->getRebrickableSetNumber()}/minifigs/", [
                'page_size' => 100,
            ]);

            if (isset($response['results'])) {
                SetMinifig::syncBySet($set, $response['results']);
            }
        }
    }

    private function sendRequest(string $url, array $data = [], string $method = 'GET'): array
    {
        $request = $this->client->createRequest()
            ->addHeaders(['Authorization' => "key " . Yii::$app->params['rebrickable.apiKey']])
            ->addHeaders(['Accept' => 'application/json'])
            ->setUrl($url)
            ->setData($data)
            ->setMethod($method);

        $response = $request->send();

        if ($response->isOk) {
            return $response->getData();
        }

        throw new Exception("There was problem with rebrickable action {$url}: {$response->getContent()}");
    }
}