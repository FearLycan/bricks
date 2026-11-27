<?php

namespace frontend\controllers;

use common\components\ConsoleRunner;
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
                    'console-run'              => ['post'],
                    'console-status'           => ['get'],
                    'console-index'            => ['get'],
                    'console-history'          => ['get'],
                    'console-view'             => ['get'],
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

    /*
     * Background runner for allowlisted console commands (common\components\ConsoleRunner):
     *
     *   POST /management/console-run     route=sitemap/generate, options={"print":1} (JSON), CSRF -> {ok, id}
     *   GET  /management/console-status  id=...[, tail=bytes] -> run meta + status + log tail
     *   GET  /management/console-index   allowlist + recent runs
     *   GET  /management/console-history page with past runs; console-view?id=... one log
     *
     * Admins only (access rule above), off unless params['console.enabled'] in
     * frontend/config/params-local.php. Every start is logged to runtime/logs/admin-console.log.
     */

    public function actionConsoleIndex(): array
    {
        $this->requireConsole(true);

        return ['ok' => true, 'allowed' => ConsoleRunner::ALLOWED, 'recent' => (new ConsoleRunner())->recent(20)];
    }

    public function actionConsoleRun(): array
    {
        $this->requireConsole(true);
        $route = trim((string)Yii::$app->request->post('route', ''));
        $raw = Yii::$app->request->post('options', '{}');
        $who = '#' . (Yii::$app->user->id ?? '?');

        try {
            $options = is_array($raw) ? $raw : \yii\helpers\Json::decode((string)$raw);
            if (!is_array($options)) {
                $options = [];
            }
            $id = (new ConsoleRunner())->start($route, $options, $who);
        } catch (\Throwable $e) {
            Yii::info("{$who} REFUSED {$route} " . \yii\helpers\Json::encode($raw) . ': ' . $e->getMessage(), 'adminConsole');
            Yii::$app->response->statusCode = 400;

            return ['ok' => false, 'error' => $e->getMessage()];
        }
        Yii::info("{$who} START {$id} {$route} " . \yii\helpers\Json::encode($options), 'adminConsole');

        return ['ok' => true, 'id' => $id];
    }

    public function actionConsoleStatus(string $id, int $tail = 65536): array
    {
        $this->requireConsole(true);
        $run = (new ConsoleRunner())->status($id, $tail);
        if ($run === null) {
            Yii::$app->response->statusCode = 404;

            return ['ok' => false, 'error' => 'Unknown run.'];
        }

        return ['ok' => true] + $run;
    }

    public function actionConsoleHistory(): string
    {
        $this->requireConsole(false);

        return $this->render('console-history', ['runs' => (new ConsoleRunner())->recent(200)]);
    }

    public function actionConsoleView(string $id): string
    {
        $this->requireConsole(false);
        $run = (new ConsoleRunner())->status($id, 512 * 1024);
        if ($run === null) {
            throw new \yii\web\NotFoundHttpException('Unknown run.');
        }

        return $this->render('console-view', ['run' => $run]);
    }

    private function requireConsole(bool $json): void
    {
        if (!(Yii::$app->params['console.enabled'] ?? false)) {
            throw new \yii\web\NotFoundHttpException('Console runner is disabled.');
        }
        if ($json) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }
    }
}
