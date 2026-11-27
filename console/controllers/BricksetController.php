<?php

namespace console\controllers;

use common\enums\image\KindEnum;
use common\enums\image\TypeEnum;
use common\enums\StatusEnum;
use common\models\Set;
use common\models\SetImage;
use common\models\SetInstruction;
use common\models\SetMinifig;
use common\models\SetPrice;
use common\models\SetTag;
use common\models\Theme;
use common\models\ThemeGroup;
use Throwable;
use Yii;
use yii\caching\CacheInterface;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Json;
use yii\httpclient\Client;

/**
 * Brickset API v3. Keys have a daily call limit (100 getSets calls for a personal key), so:
 * the user hash is kept for 30 days, pages stop when a page is not full, additional images
 * and instructions are only fetched when Brickset's counts differ from ours, and after
 * "API limit exceeded" the sync pauses (LIMIT_PAUSE) instead of failing every run.
 *
 * @property-read string $userHash
 */
class BricksetController extends Controller
{
    private const PAGE_SIZE = 500;
    private const HASH_TTL = 30 * 86400;
    private const LIMIT_PAUSE = 6 * 3600;
    private const LIMIT_KEY = 'brickset-limit-hit';

    public Client $client;
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
                    'year'         => $year,
                    'pageSize'     => self::PAGE_SIZE,
                    'pageNumber'   => $page++,
                    'extendedData' => 1,
                ]),
            ]);

            $sets = $response['sets'] ?? [];
            if ($sets) {
                $this->syncSets($sets);
            }
        } while (count($sets) >= self::PAGE_SIZE);
    }

    public function actionSyncRecentlyUpdatedSets(): int
    {
        if ($this->limitPaused()) {
            return ExitCode::OK;
        }
        $updatedSince = (new \DateTimeImmutable('-2 days', new \DateTimeZone('UTC')))->format('Y-m-d');

        $page = 1;
        try {
            do {
                $response = $this->sendRequest('getSets', [
                    'params' => Json::encode([
                        'updatedSince' => $updatedSince,
                        'pageSize'     => self::PAGE_SIZE,
                        'pageNumber'   => $page++,
                        'extendedData' => 1,
                    ]),
                ]);

                $sets = $response['sets'] ?? [];
                if ($sets) {
                    $this->syncSets($sets);
                }
            } while (count($sets) >= self::PAGE_SIZE);
        } catch (Exception $e) {
            if (!self::isLimitError($e)) {
                throw $e;
            }
            $this->pauseForLimit($e);
        }

        $controller = new ThemeController(Yii::$app->controller->id, Yii::$app);
        $controller->actionRecountSets();

        $controller = new TagController(Yii::$app->controller->id, Yii::$app);
        $controller->actionRecountSets();

        return ExitCode::OK;
    }

    /**
     * Calls per day for our key over the last 30 days (getKeyUsageStats does not count
     * against the limit and needs no login).
     */
    public function actionUsage(): int
    {
        $response = $this->client->createRequest()->setUrl('getKeyUsageStats')
            ->setData(['apiKey' => Yii::$app->params['brickset.apiKey']])->setMethod('GET')->send();
        $data = $response->isOk ? $response->getData() : [];
        if (($data['status'] ?? '') !== 'success') {
            $this->stderr('Brickset: ' . $response->getContent() . "\n");

            return ExitCode::UNAVAILABLE;
        }
        foreach ((array)($data['apiKeyUsage'] ?? []) as $row) {
            $this->stdout(sprintf("%s  %d\n", substr((string)($row['dateStamp'] ?? '?'), 0, 10), (int)($row['count'] ?? 0)));
        }
        $paused = $this->cache->get(self::LIMIT_KEY);
        if ($paused) {
            $this->stdout('Sync paused after "API limit exceeded" until ' . date('Y-m-d H:i', (int)$paused) . "\n");
        }

        return ExitCode::OK;
    }

    private function limitPaused(): bool
    {
        $until = (int)$this->cache->get(self::LIMIT_KEY);
        if ($until > time()) {
            $this->stdout('Brickset limit was hit, sync paused until ' . date('Y-m-d H:i', $until) . "\n");

            return true;
        }

        return false;
    }

    private static function isLimitError(Throwable $e): bool
    {
        return stripos($e->getMessage(), 'limit exceeded') !== false;
    }

    /** A warning, not an error: health/check reports stale sets if this lasts for days. */
    private function pauseForLimit(Throwable $e): void
    {
        $until = time() + self::LIMIT_PAUSE;
        $this->cache->set(self::LIMIT_KEY, $until, self::LIMIT_PAUSE);
        $this->stdout("Brickset API limit exceeded, pausing sync until " . date('Y-m-d H:i', $until) . "\n");
        Yii::warning('Brickset API limit exceeded, sync paused until ' . date('Y-m-d H:i', $until) . ': ' . $e->getMessage(), __METHOD__);
    }

    private function syncSets(array $sets): void
    {
        foreach ($sets as $set) {
            try {
                $this->syncSingleSet($set);
            } catch (Throwable $e) {
                if (self::isLimitError($e)) {
                    throw $e;
                }
                $setLabel = ($set['number'] ?? '?') . ' ' . ($set['name'] ?? '');
                echo "ERROR syncing set {$setLabel}: {$e->getMessage()}\n";
                Yii::error("BricksetController::syncSets failed for {$setLabel}: {$e->getMessage()}\n{$e->getTraceAsString()}", __METHOD__);
            }

            sleep(1);
        }
    }

    private function syncSingleSet(array $set): void
    {
        $themeGroup = null;
        $subTheme = null;

        echo $set['name'] . "\n";

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
        $legoSet->subtheme_id = $subTheme->id ?? null;
        $legoSet->number = $set['number'];
        $legoSet->number_variant = $set['numberVariant'] ?? 0;
        $legoSet->year = $set['year'];
        $legoSet->released = isset($set['released']) ? (int)$set['released'] : 0;
        $legoSet->pieces = $set['pieces'] ?? 0;
        $legoSet->minifigures = $set['minifigs'] ?? 0;
        $legoSet->brickset_url = $set['bricksetURL'];
        $legoSet->brickset_id = isset($set['setID']) && is_numeric($set['setID']) ? (int)$set['setID'] : $legoSet->brickset_id;
        $legoSet->availability = $set['availability'] ?? null;
        $legoSet->description = null;
        if (isset($set['extendedData']['description']) && is_string($set['extendedData']['description'])) {
            $legoSet->description = $set['extendedData']['description'];
        }

        $legoSet->dimensions = null;
        if (isset($set['modelDimensions']) && is_array($set['modelDimensions'])) {
            $legoSet->dimensions = Json::encode($set['modelDimensions']);
        }

        $legoSet->price = null;
        if (isset($set['LEGOCom']['US']['retailPrice']) && is_numeric($set['LEGOCom']['US']['retailPrice'])) {
            $legoSet->price = (int)round(((float)$set['LEGOCom']['US']['retailPrice']) * 100);
        }

        $legoSet->launch_date = $this->parseBricksetDate($set['launchDate'] ?? null)
            ?? $this->parseBricksetDate($set['LEGOCom']['US']['dateFirstAvailable'] ?? null);
        $legoSet->exit_date = $this->parseBricksetDate($set['exitDate'] ?? null);

        $legoSet->age = $set['ageRange']['min'] ?? 0;
        $legoSet->rating = $set['rating'] ?? 0;
        if (!$legoSet->save()) {
            echo "  warn: save failed: " . Json::encode($legoSet->getErrors()) . "\n";
            return;
        }
        $legoSet->refresh();

        $this->safeStep("LEGOCom prices for {$legoSet->number}", function () use ($legoSet, $set) {
            if (isset($set['LEGOCom']) && is_array($set['LEGOCom'])) {
                SetPrice::syncLegoComPrices($legoSet, $set['LEGOCom']);
            }
        });

        $this->safeStep("tags for {$legoSet->number}", function () use ($legoSet, $set) {
            SetTag::syncBySetAndNames($legoSet, isset($set['extendedData']['tags']) && is_array($set['extendedData']['tags']) ? $set['extendedData']['tags'] : []);
        });

        $this->safeStep("main image for {$legoSet->number}", function () use ($legoSet, $set) {
            if (isset($set['image']['imageURL']) && $set['image']['imageURL']) {
                SetImage::getOrCreate($legoSet, TypeEnum::IMAGE, KindEnum::MAIN, $set['image']['imageURL']);
            }
        });

        $this->safeStep("additional images for {$legoSet->number}", function () use ($legoSet, $set) {
            // getSets says how many extra images Brickset has; only ask for them when that differs.
            $remote = isset($set['additionalImageCount']) ? (int)$set['additionalImageCount'] : null;
            if ($remote === 0) {
                return;
            }
            if ($remote !== null && $remote <= (int)SetImage::find()->where(['set_id' => $legoSet->id, 'kind' => KindEnum::ADDITIONAL->value])->count()) {
                return;
            }
            $this->syncImages((int)$set['setID'], $legoSet);
        });

        if ($legoSet->getMainImage() === null) {
            $legoSet->updateAttributes(['status' => StatusEnum::INACTIVE->value]);
        } else {
            if ((int)$legoSet->status !== StatusEnum::ACTIVE->value) {
                $legoSet->rebuildSlug();
            }
            $legoSet->updateAttributes(['status' => StatusEnum::ACTIVE->value]);
        }

        if ($legoSet->isActive()) {
            $this->safeStep("minifigs for {$legoSet->number}", function () use ($legoSet, $set) {
                $controller = new RebrickableController(Yii::$app->controller->id, Yii::$app);
                $controller->actionSyncMinifigs($set['number']);

                $actualCount = (int)SetMinifig::find()->where(['set_id' => $legoSet->id])->count();
                if ($actualCount !== (int)$legoSet->minifigures) {
                    $legoSet->updateAttributes(['minifigures' => $actualCount]);
                }
            });

            $this->safeStep("instructions for {$legoSet->number}", function () use ($legoSet, $set) {
                $remote = isset($set['instructionsCount']) ? (int)$set['instructionsCount'] : null;
                if ($remote !== null && $remote === (int)SetInstruction::find()->where(['set_id' => $legoSet->id])->count()) {
                    return;
                }
                $this->syncInstructionsFor($legoSet);
            });
        }
    }

    private function safeStep(string $label, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            if (self::isLimitError($e)) {
                throw $e;
            }
            echo "  warn: {$label} failed: {$e->getMessage()}\n";
            Yii::error("BricksetController step '{$label}' failed: {$e->getMessage()}\n{$e->getTraceAsString()}", __METHOD__);
        }
    }

    private function syncImages(int $setID, Set $legoSet): void
    {
        $response = $this->sendRequest('getAdditionalImages', ['setID' => $setID]);
        $images = $response['additionalImages'] ?? [];

        foreach ($images as $key => $image) {
            if (isset($image['imageURL']) && $image['imageURL']) {
                SetImage::getOrCreate($legoSet, TypeEnum::IMAGE, KindEnum::ADDITIONAL, $image['imageURL']);
            }
        }
    }

    public function actionSyncInstructions(?string $setNumber = null): void
    {
        $query = Set::find()->andWhere(['not', ['brickset_id' => null]]);

        if ($setNumber !== null) {
            $query->andWhere(['number' => $setNumber]);
        }

        /** @var Set $set */
        foreach ($query->each() as $set) {
            echo $set->name . " sync instructions \n";
            try {
                $this->syncInstructionsFor($set);
            } catch (Throwable $e) {
                echo "  warn: instructions for {$set->number} failed: {$e->getMessage()}\n";
                Yii::error("actionSyncInstructions failed for {$set->number}: {$e->getMessage()}", __METHOD__);
            }
            sleep(1);
        }
    }

    private function syncInstructionsFor(Set $set): void
    {
        if ($set->brickset_id === null) {
            return;
        }

        $response = $this->sendRequest('getInstructions', ['setID' => $set->brickset_id]);
        $instructions = $response['instructions'] ?? [];

        SetInstruction::syncBySet($set, is_array($instructions) ? $instructions : []);
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
            $legoTheme->sets_count = (int)($theme['setCount'] ?? 0);
            $legoTheme->year_to = (int)($theme['yearTo'] ?? 0);
            $legoTheme->year_from = (int)($theme['yearFrom'] ?? 0);
            $legoTheme->save();
        }
    }

    private function parseBricksetDate(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $date = date_create($value);
        if ($date === false) {
            return null;
        }

        return $date->format('Y-m-d');
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
            $data = $response->getData();
            if (is_array($data) && ($data['status'] ?? '') !== 'error') {
                return $data;
            }
        }

        if (stripos($response->getContent(), 'hash') !== false) {
            $this->cache->delete('brickset-user-hash'); // log in again next time
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
        }, self::HASH_TTL);
    }
}
