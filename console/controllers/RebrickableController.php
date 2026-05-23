<?php

namespace console\controllers;

use common\models\Set;
use common\models\SetMinifig;
use Random\RandomException;
use Throwable;
use Yii;
use yii\caching\CacheInterface;
use yii\console\Controller;
use yii\console\Exception;
use yii\httpclient\Client;

class RebrickableController extends Controller
{
    public Client $client;
    public CacheInterface $cache;

    public function __construct($id, $module, $config = [])
    {
        $this->client = new Client(['baseUrl' => 'https://rebrickable.com/api/v3']);
        $this->cache = Yii::$app->cache;
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function actionSyncMinifigs(?string $setNumber = null): void
    {
        $sets = Set::find();

        if ($setNumber !== null) {
            $sets->andWhere(['number' => $setNumber]);
        }

        /** @var Set $set */
        foreach ($sets->each() as $set) {
            echo $set->name . " sync minifigs \n";
            try {
                $response = $this->sendRequest("lego/sets/{$set->getRebrickableSetNumber()}/minifigs/", [
                    'page_size' => 100,
                ]);

                if (isset($response['results']) && is_array($response['results']) && count($response['results']) > 0) {
                    SetMinifig::syncBySet($set, $response['results']);
                }
            } catch (Throwable $e) {
                echo "  warn: minifigs for {$set->number} failed: {$e->getMessage()}\n";
                Yii::error("actionSyncMinifigs failed for {$set->number}: {$e->getMessage()}", __METHOD__);
            }

            sleep(random_int(2, 6));
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