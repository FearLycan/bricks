<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use common\components\ConsoleRunner;
use common\components\ReadOnlySql;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Admin tools used by Claude (and by hand):
 *
 *   GET  /admin/console/index        page: commands and SQL queries run here, newest first
 *   GET  /admin/console/view?id=…    page: one command with its full output
 *   POST /admin/console/run          route=…, options={…} (JSON), CSRF -> {ok, id}   (ConsoleRunner allowlist)
 *   GET  /admin/console/status?id=…  run meta + status + log tail (JSON)
 *   GET  /admin/console/list         allowlist + recent runs + web PHP config (JSON)
 *   POST /admin/console/db-query     sql=…, CSRF -> rows (JSON)                    (ReadOnlySql rules)
 *
 * Admins only (backend\components\Controller). Command runner off unless
 * params['console.enabled'], SQL off unless params['dbQuery.enabled'] (backend/config/main.php
 * also reads both from frontend/config/params-local.php). Every start and query is logged to
 * backend/runtime/logs/admin-console.log and db-query.log.
 */
class ConsoleController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => ['run' => ['POST'], 'db-query' => ['POST'], 'status' => ['GET'], 'list' => ['GET']],
            ],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (in_array($action->id, ['run', 'status', 'list', 'db-query'], true)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        return true;
    }

    /** Activity page: console runs and SQL queries. */
    public function actionIndex(): string
    {
        return $this->render('index', [
            'runs'    => $this->consoleEnabled() ? (new ConsoleRunner())->recent(200) : [],
            'queries' => self::queries(300),
            'enabled' => ['console' => $this->consoleEnabled(), 'sql' => (bool)(Yii::$app->params['dbQuery.enabled'] ?? false)],
        ]);
    }

    public function actionView(string $id): string
    {
        $this->requireConsole();
        $run = (new ConsoleRunner())->status($id, 512 * 1024);
        if ($run === null) {
            throw new NotFoundHttpException('Unknown run.');
        }

        return $this->render('view', ['run' => $run]);
    }

    public function actionList(): array
    {
        $this->requireConsole();

        return [
            'ok'      => true,
            'allowed' => ConsoleRunner::ALLOWED,
            'recent'  => (new ConsoleRunner())->recent(20),
            'php'     => [
                'version'          => PHP_VERSION,
                'sapi'             => PHP_SAPI,
                'iniLoaded'        => php_ini_loaded_file(),
                'disableFunctions' => ini_get('disable_functions'),
            ],
        ];
    }

    public function actionRun(): array
    {
        $this->requireConsole();
        $route = trim((string)Yii::$app->request->post('route', ''));
        $raw = Yii::$app->request->post('options', '{}');
        $who = $this->who();

        try {
            $options = is_array($raw) ? $raw : Json::decode((string)$raw);
            if (!is_array($options)) {
                $options = [];
            }
            $id = (new ConsoleRunner())->start($route, $options, $who);
        } catch (\Throwable $e) {
            Yii::info("{$who} REFUSED {$route} " . Json::encode($raw) . ': ' . $e->getMessage(), 'adminConsole');
            Yii::$app->response->statusCode = 400;

            return ['ok' => false, 'error' => $e->getMessage()];
        }
        Yii::info("{$who} START {$id} {$route} " . Json::encode($options), 'adminConsole');

        return ['ok' => true, 'id' => $id];
    }

    public function actionStatus(string $id, int $tail = 65536): array
    {
        $this->requireConsole();
        $run = (new ConsoleRunner())->status($id, $tail);
        if ($run === null) {
            Yii::$app->response->statusCode = 404;

            return ['ok' => false, 'error' => 'Unknown run.'];
        }

        return ['ok' => true] + $run;
    }

    public function actionDbQuery(): array
    {
        if (!(Yii::$app->params['dbQuery.enabled'] ?? false)) {
            Yii::$app->response->statusCode = 404;

            return ['ok' => false, 'error' => 'Disabled.'];
        }
        $sql = trim((string)Yii::$app->request->post('sql', ''));
        $who = $this->who();

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

    /** Backend log plus the older frontend one (from before the tools moved here), newest first. */
    private static function queries(int $limit): array
    {
        $all = array_merge(
            self::readQueryLog(Yii::getAlias('@runtime/logs/db-query.log'), $limit),
            self::readQueryLog(Yii::getAlias('@frontend/runtime/logs/db-query.log'), $limit)
        );
        usort($all, static fn(array $a, array $b): int => strcmp($b['time'], $a['time']));

        return array_slice($all, 0, $limit);
    }

    private function consoleEnabled(): bool
    {
        return (bool)(Yii::$app->params['console.enabled'] ?? false);
    }

    private function requireConsole(): void
    {
        if (!$this->consoleEnabled()) {
            throw new NotFoundHttpException('Console runner is disabled.');
        }
    }

    private function who(): string
    {
        $user = Yii::$app->user->identity;

        return $user instanceof User ? '#' . $user->id : '?';
    }

    /**
     * Last $limit entries of the db-query audit log, newest first. Lines without a timestamp
     * continue the previous message (multi-line SQL).
     *
     * @return list<array{time: string, who: string, result: string, detail: string, sql: string}>
     */
    private static function readQueryLog(string $file, int $limit): array
    {
        if (!is_file($file) || ($fh = fopen($file, 'rb')) === false) {
            return [];
        }
        if ((int)filesize($file) > 512 * 1024) {
            fseek($fh, -512 * 1024, SEEK_END);
            fgets($fh);
        }
        $entries = [];
        while (($line = fgets($fh)) !== false) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) \[[^\]]*\]\[[^\]]*\]\[[^\]]*\]\[info\]\[dbQuery\] (.*)$/s', $line, $m)) {
                $entries[] = ['time' => $m[1], 'raw' => rtrim($m[2])];
            } elseif ($entries !== [] && !preg_match('/^\s+in \S+:\d+$/', rtrim($line))) {
                $entries[count($entries) - 1]['raw'] .= "\n" . rtrim($line);
            }
        }
        fclose($fh);

        $out = [];
        foreach (array_reverse(array_slice($entries, -$limit)) as $e) {
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
}
