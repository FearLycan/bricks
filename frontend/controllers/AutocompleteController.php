<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\Set;
use Yii;
use yii\web\Response;

class AutocompleteController extends Controller
{
    private const PAGE_SIZE = 20;


    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['theme', 'year'],
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function __construct($id, $module, $config = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        parent::__construct($id, $module, $config);
    }

    public function actionTheme(string $term = '', int $page = 1): array
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $page = max(1, $page);
        $term = trim($term);

        return $this->buildThemeResponse($term, $page);
    }

    public function actionYear(string $term = '', int $page = 1): array
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $page = max(1, $page);
        $term = trim($term);

        return $this->buildYearResponse($term, $page);
    }

    private function buildThemeResponse(string $term, int $page): array
    {
        $themes = Set::getAvailableThemesList();
        $startIndex = ($page - 1) * self::PAGE_SIZE;
        $matchedCount = 0;
        $results = [];
        $hasMore = false;

        foreach ($themes as $id => $name) {
            $nameText = (string)$name;
            if ($term !== '' && stripos($nameText, $term) === false) {
                continue;
            }

            if ($matchedCount++ < $startIndex) {
                continue;
            }

            if (count($results) >= self::PAGE_SIZE) {
                $hasMore = true;
                break;
            }

            $results[] = [
                'id'   => (int)$id,
                'text' => $nameText,
            ];
        }

        return [
            'results'    => $results,
            'pagination' => ['more' => $hasMore],
        ];
    }

    private function buildYearResponse(string $term, int $page): array
    {
        $years = array_map('intval', array_keys(Set::getAvailableYearsList()));
        $startIndex = ($page - 1) * self::PAGE_SIZE;
        $matchedCount = 0;
        $results = [];
        $hasMore = false;

        foreach ($years as $year) {
            if ($term !== '' && !str_contains((string)$year, $term)) {
                continue;
            }
            if ($matchedCount++ < $startIndex) {
                continue;
            }
            if (count($results) >= self::PAGE_SIZE) {
                $hasMore = true;
                break;
            }
            $results[] = [
                'id'   => $year,
                'text' => (string)$year,
            ];
        }

        return [
            'results'    => $results,
            'pagination' => ['more' => $hasMore],
        ];
    }

}
