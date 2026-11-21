<?php

namespace frontend\controllers;

use common\components\ReadOnlySql;
use common\models\User;
use frontend\components\T;
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
                    'db-query'                 => ['post'],
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
                'message' => T::tr('Import link queued.'),
            ];
        }

        return [
            'success' => false,
            'message' => '',
            'errors'  => $model->getErrors(),
        ];
    }

    /**
     * Read-only SQL for diagnostics (admins only, off unless
     * params['dbQuery.enabled']). POST sql=...; JSON out. Every call is
     * logged to runtime/logs/db-query.log. Rules: common\components\ReadOnlySql.
     */
    public function actionDbQuery(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!(Yii::$app->params['dbQuery.enabled'] ?? false)) {
            Yii::$app->response->statusCode = 404;
            return ['ok' => false, 'error' => 'Disabled.'];
        }

        $sql = trim((string)Yii::$app->request->post('sql', ''));
        $user = Yii::$app->user->identity;
        $who = $user instanceof User ? ('#' . $user->id) : '?';

        $error = ReadOnlySql::validate($sql, Yii::$app->db->schema->getTableNames());
        if ($error !== null) {
            Yii::info("{$who} REFUSED ({$error}): {$sql}", 'dbQuery');
            Yii::$app->response->statusCode = 400;
            return ['ok' => false, 'error' => $error];
        }

        try {
            $result = ReadOnlySql::run($sql);
        } catch (\Throwable $e) {
            Yii::info("{$who} ERROR: {$sql} | " . $e->getMessage(), 'dbQuery');
            Yii::$app->response->statusCode = 400;
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        Yii::info(sprintf('%s OK %d rows %d ms: %s', $who, count($result['rows']), $result['ms'], $sql), 'dbQuery');

        return ['ok' => true] + $result;
    }
}
