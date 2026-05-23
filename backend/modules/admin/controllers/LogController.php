<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use backend\modules\admin\models\LogSearch;
use common\models\Log;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class LogController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete'         => ['POST'],
                    'clear-resolved' => ['POST'],
                    'clear-all'      => ['POST'],
                    'mark-resolved'  => ['POST'],
                    'mark-all'       => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex(): string
    {
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $totals = [
            'total'     => (int)Log::find()->count(),
            'errors'    => (int)Log::find()->where(['level' => Log::LEVEL_ERROR, 'resolved_at' => null])->count(),
            'warnings'  => (int)Log::find()->where(['level' => Log::LEVEL_WARNING, 'resolved_at' => null])->count(),
            'resolved'  => (int)Log::find()->where(['not', ['resolved_at' => null]])->count(),
        ];

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'totals'       => $totals,
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete(int $id): \yii\web\Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Log entry deleted.');

        return $this->redirect(['index']);
    }

    public function actionMarkResolved(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);
        if (!$model->isResolved()) {
            $model->resolved_at = date('Y-m-d H:i:s');
            $model->save(false, ['resolved_at']);
        }
        Yii::$app->session->setFlash('success', "Entry #{$model->id} marked as resolved.");

        return $this->redirect($this->request->post('returnUrl', ['index']));
    }

    public function actionMarkAll(): \yii\web\Response
    {
        $affected = Log::updateAll(
            ['resolved_at' => date('Y-m-d H:i:s')],
            ['resolved_at' => null]
        );
        Yii::$app->session->setFlash('success', "Marked {$affected} entries as resolved.");

        return $this->redirect(['index']);
    }

    public function actionClearResolved(): \yii\web\Response
    {
        $affected = Log::deleteAll(['not', ['resolved_at' => null]]);
        Yii::$app->session->setFlash('success', "Deleted {$affected} resolved entries.");

        return $this->redirect(['index']);
    }

    public function actionClearAll(): \yii\web\Response
    {
        $affected = Log::deleteAll();
        Yii::$app->session->setFlash('success', "Deleted {$affected} log entries.");

        return $this->redirect(['index']);
    }

    protected function findModel(int $id): Log
    {
        $model = Log::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('The requested log entry does not exist.');
        }

        return $model;
    }
}
