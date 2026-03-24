<?php

namespace frontend\controllers;

use common\models\User;
use frontend\models\QueueOfferImportForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class ManagementController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'         => true,
                        'roles'         => ['@'],
                        'matchCallback' => static function (): bool {
                            $identity = Yii::$app->user->identity;
                            return $identity instanceof User && $identity->isAdmin();
                        },
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'queue-offer-import-modal' => ['get'],
                    'queue-offer-import'       => ['post'],
                ],
            ],
        ];
    }

    public function actionQueueOfferImportModal(int $setId): string
    {
        $model = new QueueOfferImportForm();
        $model->setId = $setId;

        return $this->renderAjax('/management/_queue-offer-import-modal', [
            'model' => $model,
        ]);
    }

    public function actionQueueOfferImport(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new QueueOfferImportForm();
        $model->load(Yii::$app->request->post());

        if ($model->saveToQueue()) {
            return [
                'success' => true,
                'message' => 'Import link queued.',
            ];
        }

        return [
            'success' => false,
            'message' => '',
            'errors'  => $model->getErrors(),
        ];
    }
}
