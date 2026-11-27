<?php

declare(strict_types=1);

namespace common\components;

use InvalidArgumentException;
use RuntimeException;
use Yii;
use yii\helpers\Json;

/**
 * Starts allowlisted `php yii …` commands in the background for the admin console endpoint and
 * reports on them. Nothing outside {@see ALLOWED} can run: the route must be listed, each option
 * must be listed for that route and match its type, and the command line is built here from the
 * validated values (every piece through escapeshellarg). No shell; migrations only through
 * deploy/run (fast-forward pull of origin/master, then migrate/up). Ported from store-base.
 *
 * Each run gets console/runtime/console-runs/<id>.json (meta), <id>.log (stdout+stderr) and, once the
 * process exits, <id>.exit with the exit code.
 */
final class ConsoleRunner
{
    /**
     * route => [option => type]. Types: bool, int, date (Y-m-d), digits. Key `_args` lists
     * positional arguments in order, with their types.
     */
    public const ALLOWED = [
        'sitemap/generate'                     => [],
        'index-stats'                          => [],
        'cache/flush-all'                      => [],
        'health/check'                         => ['print' => 'bool'],
        'schedule/list'                        => [],
        'tag/recount-sets'                     => [],
        'theme/recount-sets'                   => [],
        'bot-guard/update-ranges'              => [],
        'fingerprint/report'                   => ['_args' => ['hours' => 'int']],
        'brickset/sync-recently-updated-sets'  => [],
        'brickset/sync-themes'                 => [],
        'set/list-broken-slugs'                => [],
        // git pull --ff-only origin master + migrate/up (console/controllers/DeployController).
        'deploy/status'                        => [],
        'deploy/run'                           => [],
    ];

    /** A run with no exit file after this long is reported as lost (killed, server restart). */
    private const LOST_AFTER = 6 * 3600;

    private const LOG_TAIL_BYTES = 64 * 1024;

    /**
     * Validates and starts one command. Returns the run id.
     *
     * @param array<string,mixed> $options
     */
    public function start(string $route, array $options, string $who): string
    {
        $argv = $this->buildArgv($route, $options);

        if (!$this->canExec()) {
            throw new RuntimeException('exec() is disabled in this PHP configuration.');
        }
        foreach ($this->recent(50) as $run) {
            if ($run['route'] === $route && $run['status'] === 'running') {
                throw new RuntimeException("{$route} is already running (run {$run['id']}).");
            }
        }

        $dir = $this->dir();
        $id = date('Ymd-His') . '-' . bin2hex(random_bytes(3));
        $base = $dir . '/' . $id;

        file_put_contents($base . '.json', Json::encode([
            'id'      => $id,
            'route'   => $route,
            'options' => $options,
            'command' => 'php yii ' . implode(' ', $argv),
            'who'     => $who,
            'started' => time(),
        ]));

        $yii = implode(' ', array_map('escapeshellarg', array_merge([$this->phpBinary(), 'yii'], $argv)));
        $inner = sprintf(
            'cd %s && %s > %s 2>&1; echo $? > %s',
            escapeshellarg(self::rootDir()),
            $yii,
            escapeshellarg($base . '.log'),
            escapeshellarg($base . '.exit'),
        );
        exec('nohup sh -c ' . escapeshellarg($inner) . ' > /dev/null 2>&1 &');

        return $id;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function status(string $id, int $tailBytes = self::LOG_TAIL_BYTES): ?array
    {
        if (!preg_match('/^\d{8}-\d{6}-[0-9a-f]{6}$/', $id)) {
            return null;
        }
        $run = $this->meta($id);
        if ($run === null) {
            return null;
        }
        $log = $this->dir() . '/' . $id . '.log';
        $run['log'] = '';
        $run['logBytes'] = is_file($log) ? (int) filesize($log) : 0;
        if ($run['logBytes'] > 0) {
            $tailBytes = max(1024, min($tailBytes, 512 * 1024));
            $fh = fopen($log, 'rb');
            if ($fh !== false) {
                if ($run['logBytes'] > $tailBytes) {
                    fseek($fh, -$tailBytes, SEEK_END);
                }
                $run['log'] = (string) stream_get_contents($fh);
                fclose($fh);
            }
        }

        return $run;
    }

    /**
     * Newest first, without logs.
     *
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 20): array
    {
        $files = glob($this->dir() . '/*.json') ?: [];
        rsort($files);
        $out = [];
        foreach (array_slice($files, 0, $limit) as $file) {
            $run = $this->meta(basename($file, '.json'));
            if ($run !== null) {
                $out[] = $run;
            }
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $options
     * @return array<int,string>
     */
    public function buildArgv(string $route, array $options): array
    {
        if (!isset(self::ALLOWED[$route])) {
            throw new InvalidArgumentException("Command not allowed: {$route}");
        }
        $spec = self::ALLOWED[$route];
        $positional = $spec['_args'] ?? [];
        unset($spec['_args']);

        $argv = [$route];
        foreach ($positional as $name => $type) {
            if (!array_key_exists($name, $options)) {
                throw new InvalidArgumentException("Missing argument: {$name}");
            }
            $argv[] = $this->checked($name, $type, $options[$name]);
            unset($options[$name]);
        }
        foreach ($options as $name => $value) {
            if (!isset($spec[$name])) {
                throw new InvalidArgumentException("Option not allowed for {$route}: {$name}");
            }
            $argv[] = '--' . $name . '=' . $this->checked($name, $spec[$name], $value);
        }

        return $argv;
    }

    private function checked(string $name, string $type, mixed $value): string
    {
        $value = is_bool($value) ? ($value ? '1' : '0') : trim((string) $value);
        $ok = match ($type) {
            'bool'   => in_array($value, ['0', '1'], true),
            'int'    => preg_match('/^\d{1,7}$/', $value) === 1,
            'digits' => preg_match('/^\d{1,20}$/', $value) === 1,
            'date'   => preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && strtotime($value) !== false,
            default  => false,
        };
        if (!$ok) {
            throw new InvalidArgumentException("Invalid value for {$name} ({$type}).");
        }

        return $value;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function meta(string $id): ?array
    {
        $base = $this->dir() . '/' . $id;
        $raw = @file_get_contents($base . '.json');
        if ($raw === false) {
            return null;
        }
        try {
            $run = Json::decode($raw);
        } catch (\Throwable) {
            return null;
        }
        if (!is_array($run)) {
            return null;
        }
        if (is_file($base . '.exit')) {
            $run['exitCode'] = (int) trim((string) file_get_contents($base . '.exit'));
            $run['finished'] = (int) filemtime($base . '.exit');
            $run['status'] = $run['exitCode'] === 0 ? 'done' : 'failed';
        } else {
            $run['status'] = time() - (int) ($run['started'] ?? 0) > self::LOST_AFTER ? 'lost' : 'running';
        }

        return $run;
    }

    private function dir(): string
    {
        $dir = Yii::getAlias('@console/runtime/console-runs');
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create console/runtime/console-runs.');
        }

        return $dir;
    }

    /** Project root (where the `yii` script lives). */
    public static function rootDir(): string
    {
        return dirname(Yii::getAlias('@common'));
    }

    /** Web requests run under php-fpm, so PHP_BINARY is not the CLI; use the configured one. */
    private function phpBinary(): string
    {
        $bin = trim((string) (Yii::$app->params['console.phpBinary'] ?? ''));

        return $bin !== '' ? $bin : PHP_BINDIR . '/php';
    }

    private function canExec(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return !in_array('exec', $disabled, true);
    }
}
