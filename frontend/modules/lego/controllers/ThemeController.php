<?php

namespace frontend\modules\lego\controllers;

use common\components\AccessControl;
use common\components\Controller;
use common\models\Theme;
use frontend\models\searches\SetSearch;

class ThemeController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => [
                            'index', 'view',
                        ],
                        'roles'   => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(string $slug, ?string $sub = null): string
    {
        $theme = $this->findModel($slug);

        $subTheme = null;
        if ($sub) {
            $subTheme = $this->findModel($slug, $sub);
        }

        $searchModel = new SetSearch();

        if ($sub === null) {
            $searchModel->theme_id = $theme->id;
        }

        if ($subTheme) {
            $searchModel->subtheme_id = $subTheme->id;
        }

        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'theme'        => $theme,
            'subTheme'     => $subTheme,
        ]);
    }

    public function actionView(string $slug): string
    {
        return $this->render('view', []);
    }

    private function findModel(string $slug, ?string $sub = null): Theme
    {
        $model = Theme::find()->where(['slug' => $slug])->one();

        if ($sub && $model) {
            $model = Theme::find()->where([
                'slug'      => $sub,
                'parent_id' => $model->id,
            ])->one();
        }

        if (!$model) {
            $this->notFound();
        }

        return $model;
    }
}