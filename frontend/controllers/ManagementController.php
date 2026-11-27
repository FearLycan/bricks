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
                    'activity'                 => ['get'],
                    'traffic'                  => ['get'],
                    'traffic-data'             => ['get'],
                    'messages'                 => ['get'],
                    'message'                  => ['get'],
                    'message-unread'           => ['post'],
                    'message-delete'           => ['post'],
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

        return [
            'ok'      => true,
            'allowed' => ConsoleRunner::ALLOWED,
            'recent'  => (new ConsoleRunner())->recent(20),
            // Why a run cannot start (exec disabled etc.): what this web PHP actually loads.
            'php'     => [
                'version'           => PHP_VERSION,
                'sapi'              => PHP_SAPI,
                'binary'            => PHP_BINARY,
                'iniLoaded'         => php_ini_loaded_file(),
                'iniScanned'        => php_ini_scanned_files(),
                'userIni'           => ini_get('user_ini.filename'),
                'disableFunctions'  => ini_get('disable_functions'),
                'openBasedir'       => ini_get('open_basedir'),
            ],
        ];
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

    /**
     * What was run on this site through the admin tools: console runs (incl. deploys)
     * and read-only SQL queries, newest first. Reads the existing audit trails
     * (console/runtime/console-runs, runtime/logs/db-query.log).
     */
    public function actionActivity(): string
    {
        $runs = (Yii::$app->params['console.enabled'] ?? false) ? (new ConsoleRunner())->recent(200) : [];

        return $this->render('activity', [
            'runs'    => $runs,
            'queries' => self::readQueryLog(Yii::getAlias('@runtime/logs/db-query.log'), 300),
        ]);
    }

    /**
     * Last $limit entries of the db-query audit log, newest first.
     * Line format (yii\log\FileTarget): "Y-m-d H:i:s [ip][user][session][info][dbQuery] message";
     * lines without a timestamp continue the previous message (multi-line SQL).
     *
     * @return list<array{time: string, who: string, result: string, detail: string, sql: string}>
     */
    private static function readQueryLog(string $file, int $limit): array
    {
        if (!is_file($file)) {
            return [];
        }
        $size = (int)filesize($file);
        $fh = fopen($file, 'rb');
        if ($fh === false) {
            return [];
        }
        if ($size > 512 * 1024) {
            fseek($fh, -512 * 1024, SEEK_END);
            fgets($fh); // drop the partial first line
        }
        $entries = [];
        while (($line = fgets($fh)) !== false) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) \[[^\]]*\]\[[^\]]*\]\[[^\]]*\]\[info\]\[dbQuery\] (.*)$/s', $line, $m)) {
                $entries[] = ['time' => $m[1], 'raw' => rtrim($m[2])];
            } elseif ($entries !== []) {
                $entries[count($entries) - 1]['raw'] .= "\n" . rtrim($line);
            }
        }
        fclose($fh);

        $out = [];
        foreach (array_reverse(array_slice($entries, -$limit)) as $e) {
            // "#3 OK 12 rows 5 ms: SQL" | "#3 REFUSED (why): SQL" | "#3 ERROR: SQL | message"
            if (preg_match('/^(#\S+|\?) (OK) (\d+ rows \d+ ms): (.*)$/s', $e['raw'], $m)
                || preg_match('/^(#\S+|\?) (REFUSED) \((.*?)\): (.*)$/s', $e['raw'], $m)) {
                $out[] = ['time' => $e['time'], 'who' => $m[1], 'result' => $m[2], 'detail' => $m[3], 'sql' => trim($m[4])];
            } elseif (preg_match('/^(#\S+|\?) ERROR: (.*?) \| (.*)$/s', $e['raw'], $m)) {
                $out[] = ['time' => $e['time'], 'who' => $m[1], 'result' => 'ERROR', 'detail' => mb_substr($m[3], 0, 200), 'sql' => trim($m[2])];
            } else {
                $out[] = ['time' => $e['time'], 'who' => '', 'result' => '', 'detail' => '', 'sql' => $e['raw']];
            }
        }

        return $out;
    }

    /** Allowed traffic report periods in days (1 = today only). */
    private const TRAFFIC_RANGES = [1, 7, 30, 90, 365];

    /** Cookieless traffic stats page (a shell; data comes from actionTrafficData). */
    public function actionTraffic(int $days = 30): string
    {
        return $this->render('traffic', [
            'days'   => in_array($days, self::TRAFFIC_RANGES, true) ? $days : 30,
            'ranges' => self::TRAFFIC_RANGES,
        ]);
    }

    /** GET /management/traffic-data?days=N */
    public function actionTrafficData(int $days = 30): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Cache-Control', 'no-store');

        return (new \frontend\components\TrafficReport(in_array($days, self::TRAFFIC_RANGES, true) ? $days : 30))->toArray();
    }

    /** Contact form inbox. */
    public function actionMessages(): string
    {
        return $this->render('messages', [
            'dataProvider' => new \yii\data\ActiveDataProvider([
                'query'      => \common\models\ContactMessage::find(),
                'sort'       => ['defaultOrder' => ['id' => SORT_DESC]],
                'pagination' => ['pageSize' => 30],
            ]),
        ]);
    }

    public function actionMessage(int $id): string
    {
        $m = $this->findMessage($id);
        if ($m->read_at === null) {
            $m->read_at = time();
            $m->save(false, ['read_at']);
        }

        return $this->render('message', ['m' => $m]);
    }

    public function actionMessageUnread(int $id): \yii\web\Response
    {
        $m = $this->findMessage($id);
        $m->read_at = null;
        $m->save(false, ['read_at']);

        return $this->redirect(['/management/messages']);
    }

    public function actionMessageDelete(int $id): \yii\web\Response
    {
        $this->findMessage($id)->delete();
        Yii::$app->session->setFlash('success', 'Message deleted.');

        return $this->redirect(['/management/messages']);
    }

    private function findMessage(int $id): \common\models\ContactMessage
    {
        return \common\models\ContactMessage::findOne($id) ?? throw new \yii\web\NotFoundHttpException();
    }
}
