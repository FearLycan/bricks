<?php

namespace frontend\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\enums\StatusEnum;
use common\models\Set;
use common\models\Theme;
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
                        'actions' => ['theme', 'year', 'search'],
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
        $page = max(1, $page);
        $term = trim($term);

        return $this->buildThemeResponse($term, $page);
    }

    public function actionYear(string $term = '', int $page = 1): array
    {
        $page = max(1, $page);
        $term = trim($term);

        return $this->buildYearResponse($term, $page);
    }

    public function actionSearch(string $term = ''): array
    {
        $term = trim($term);
        if (strlen($term) < 2) {
            return ['sets' => [], 'themes' => []];
        }

        $setsQuery = Set::find()
            ->alias('s')
            ->where(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['or', ['like', 's.name', $term], ['like', 's.number', $term]]);

        $setsTotal = (int)$setsQuery->count();

        $sets = (clone $setsQuery)
            ->with(['mainImageRelation'])
            ->orderBy([
                new \yii\db\Expression('CASE WHEN s.number = :t THEN 0 ELSE 1 END', [':t' => $term]),
                's.rating' => SORT_DESC,
                's.year'   => SORT_DESC,
            ])
            ->limit(5)
            ->all();

        $themes = Theme::find()
            ->alias('t')
            ->where(['like', 't.name', $term])
            ->andWhere(['t.status' => StatusEnum::ACTIVE->value])
            ->with(['parent'])
            ->orderBy(['t.sets_count' => SORT_DESC])
            ->limit(4)
            ->all();

        return [
            'setsTotal' => $setsTotal,
            'sets'      => array_map(fn(Set $s) => [
                'name'   => $s->name,
                'number' => $s->number,
                'url'    => '/lego/' . $s->slug,
                'img'    => $s->getDisplayMainImageUrl(),
            ], $sets),
            'themes' => array_map(fn(Theme $t) => [
                'name'   => $t->name,
                'parent' => $t->parent?->name,
                'url'    => $t->parent_id !== null && $t->parent !== null
                    ? '/lego/theme/' . $t->parent->slug . '/' . $t->slug
                    : '/lego/theme/' . $t->slug,
            ], $themes),
        ];
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
