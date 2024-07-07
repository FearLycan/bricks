<?php

namespace console\controllers;

use common\enums\image\KindEnum;
use common\enums\image\TypeEnum;
use common\models\Set;
use common\models\SetImage;
use common\models\Theme;
use common\models\ThemeGroup;
use Yii;
use yii\caching\CacheInterface;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Json;
use yii\httpclient\Client;

class BricksetController extends Controller
{
    public Client         $client;
    public CacheInterface $cache;

    public function __construct($id, $module, $config = [])
    {
        $this->client = new Client(['baseUrl' => 'https://brickset.com/api/v3.asmx']);
        $this->cache = Yii::$app->cache;
        parent::__construct($id, $module, $config);
    }

    public function actionSyncSets(int $year = 2024): void
    {
        $page = 1;
        do {
            $response = $this->sendRequest('getSets', [
                'params' => Json::encode([
                    'year'       => $year,
                    'pageSize'   => 500,
                    'pageNumber' => $page++,
                ]),
            ]);

            $sets = $response['sets'] ?? [];

            foreach ($sets as $set) {

                $legoSet = Set::find()->where([
                    'number' => $set['number'],
                ])->one();

                if (!$legoSet) {
                    $legoSet = new Set();
                }

                if (isset($set['themeGroup'])) {
                    $themeGroup = ThemeGroup::getOrCreate($set['themeGroup']);
                }

                $theme = Theme::getOrCreate($set['theme'], $themeGroup ?? null);

                if (isset($set['subtheme'])) {
                    $subTheme = Theme::getOrCreateSub($set['subtheme'], $theme);
                }


                $legoSet->name = $set['name'];
                $legoSet->theme_id = $theme->id;
                $legoSet->number = $set['number'];
                $legoSet->number_variant = $set['numberVariant'] ?? 0;
                $legoSet->year = $set['year'];
                $legoSet->released = isset($set['released']) ? (int)$set['released'] : 0;
                $legoSet->pieces = $set['pieces'] ?? 0;
                $legoSet->minifigures = $set['minifigs'] ?? 0;
                $legoSet->brickset_url = $set['bricksetURL'];
                $legoSet->age = $set['ageRange']['min'] ?? 0;
                $legoSet->save();

                $this->syncImages((int)$set['setID'], $legoSet);
            }

        } while (count($sets) > 0);


    }

    private function syncImages(int $setID, Set $legoSet): void
    {
        $response = $this->sendRequest('getAdditionalImages', ['setID' => $setID]);
        $images = $response['additionalImages'] ?? [];

        foreach ($images as $key => $image) {
            if (isset($image['imageURL']) && $image['imageURL']) {

                $kind = KindEnum::ADDITIONAL;
                if ($key === 0) {
                    $kind = KindEnum::MAIN;
                }

                SetImage::getOrCreate($legoSet, TypeEnum::IMAGE, $kind, $image['imageURL']);
            }
        }

    }

    public function actionSyncThemes(): void
    {
        $response = $this->sendRequest('getThemes', []);
        $themes = $response['themes'] ?? [];

        foreach ($themes as $theme) {
            $legoTheme = Theme::findOne(['name' => $theme['theme']]);

            if (!$legoTheme) {
                $legoTheme = new Theme();
            }

            $legoTheme->name = $theme['theme'];
            $legoTheme->sets_count = $theme['setCount'];
            $legoTheme->year_to = $theme['yearTo'];
            $legoTheme->year_from = $theme['yearFrom'];
            $legoTheme->save();
        }

    }

    private function sendRequest(string $url, array $data = [], string $method = 'GET'): array
    {
        $data = array_merge($data, ['userHash' => $this->getUserHash(), 'apiKey' => Yii::$app->params['brickset.apiKey']]);

        $request = $this->client->createRequest()
            ->setUrl($url)
            ->setData($data)
            ->setMethod($method);

        $response = $request->send();

        if ($response->isOk) {
            return $response->getData();
        }

        throw new Exception("There was problem with brickset action {$url}: {$response->getContent()}");
    }

    private function getUserHash(): string
    {
        return $this->cache->getOrSet('brickset-user-hash', function () {
            $request = $this->client->createRequest()
                ->setUrl('login')
                ->setData([
                    'apiKey'   => Yii::$app->params['brickset.apiKey'],
                    'username' => Yii::$app->params['brickset.username'],
                    'password' => Yii::$app->params['brickset.password'],
                ])
                ->setMethod('GET');

            $response = $request->send();

            if ($response->isOk && isset($response->getData()['hash'])) {
                return $response->getData()['hash'];
            }

            throw new Exception("There was problem with brickset login: {$response->getContent()}");

        }, 60 * 60);
    }
}