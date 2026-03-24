<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use backend\modules\admin\models\SetOfferImportSearch;
use common\enums\SetOfferImportStatusEnum;
use common\models\SetOfferImport;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class SetOfferImportController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'accept' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new SetOfferImportSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel((int)$id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel((int)$id)->delete();

        return $this->redirect(['index']);
    }

    public function actionAccept(int $id, ?string $returnUrl = null)
    {
        $model = $this->findModel($id);
        $safeReturnUrl = $this->normalizeReturnUrl($returnUrl);
        if ($model->status !== SetOfferImportStatusEnum::AWAITING_REVIEW->value) {
            Yii::$app->session->setFlash('warning', 'Only links awaiting review can be accepted.');

            return $this->redirect($safeReturnUrl ?: ['index']);
        }

        $model->status = SetOfferImportStatusEnum::PENDING->value;
        $model->error_message = null;
        if ($model->save(false, ['status', 'error_message', 'updated_at'])) {
            Yii::$app->session->setFlash('success', 'Import link accepted and queued for processing.');
        } else {
            Yii::$app->session->setFlash('error', 'Could not accept import link.');
        }

        return $this->redirect($safeReturnUrl ?: ['index']);
    }

    private function normalizeReturnUrl(?string $returnUrl): ?string
    {
        if (!is_string($returnUrl) || trim($returnUrl) === '') {
            return null;
        }

        $normalized = trim($returnUrl);
        $parts = parse_url($normalized);
        if ($parts === false) {
            return null;
        }

        if (isset($parts['scheme']) || isset($parts['host'])) {
            return null;
        }

        return $normalized;
    }

    protected function findModel(int $id): SetOfferImport
    {
        $model = SetOfferImport::findOne(['id' => $id]);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
