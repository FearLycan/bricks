<?php

declare(strict_types=1);

namespace console\controllers;

use common\components\ConsoleRunner;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Deploys this checkout from origin/master: the same two steps as by hand on the server.
 *
 *   php yii deploy/status   fetch and show how far this checkout is behind origin/master (changes nothing)
 *   php yii deploy/run      git pull --ff-only origin master, then migrate/up in a fresh process
 *
 * Fast-forward only: with local commits, or local edits to files the new commits touch, git
 * refuses and nothing else runs. Also runnable from the admin (ConsoleRunner allowlist,
 * /management/console-run). Ported from store-base.
 */
final class DeployController extends Controller
{
    private const BRANCH = 'master';
    private const GIT_TIMEOUT = 120;
    private const MIGRATE_TIMEOUT = 600;

    public function actionStatus(): int
    {
        $fetch = $this->git(['fetch', '--quiet', 'origin', self::BRANCH]);
        if ($fetch['code'] !== 0) {
            $this->stderr("git fetch failed:\n" . $fetch['out'] . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $head = trim($this->git(['rev-parse', '--short', 'HEAD'])['out']);
        $remote = trim($this->git(['rev-parse', '--short', 'origin/' . self::BRANCH])['out']);
        $behind = trim($this->git(['rev-list', '--count', 'HEAD..origin/' . self::BRANCH])['out']);
        $ahead = trim($this->git(['rev-list', '--count', 'origin/' . self::BRANCH . '..HEAD'])['out']);
        $dirty = trim($this->git(['status', '--porcelain', '--untracked-files=no'])['out']);

        $this->stdout("This checkout: {$head}   origin/" . self::BRANCH . ": {$remote}\n");
        $this->stdout("Behind: {$behind}   Ahead (local commits): {$ahead}\n");
        if ($behind !== '0') {
            $this->stdout("\nNew commits:\n" . $this->git(['log', '--oneline', '--no-decorate', 'HEAD..origin/' . self::BRANCH])['out'] . "\n");
        }
        if ($dirty !== '') {
            $this->stdout("\nLocal changes to tracked files (kept by deploy/run; it stops if a new commit touches one):\n{$dirty}\n");
        }
        $pending = $this->yii(['migrate/new', '--interactive=0']);
        $this->stdout("\n" . trim($pending['out']) . "\n");

        return ExitCode::OK;
    }

    public function actionRun(): int
    {
        // No own dirty check: git itself refuses a pull that would overwrite a locally changed
        // file, and unrelated local edits (or files deleted on purpose) shouldn't block a deploy.
        $dirty = trim($this->git(['status', '--porcelain', '--untracked-files=no'])['out']);
        if ($dirty !== '') {
            $this->stdout("Note, local changes to tracked files (kept as they are):\n{$dirty}\n\n");
        }

        $before = trim($this->git(['rev-parse', 'HEAD'])['out']);
        $this->stdout('$ git pull --ff-only origin ' . self::BRANCH . "\n");
        $pull = $this->git(['pull', '--ff-only', 'origin', self::BRANCH], self::GIT_TIMEOUT);
        $this->stdout($pull['out'] . "\n");
        if ($pull['code'] !== 0) {
            $this->stderr("git pull failed (exit {$pull['code']}). Nothing else was run.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $after = trim($this->git(['rev-parse', 'HEAD'])['out']);
        if ($after !== $before) {
            $this->stdout($this->git(['log', '--oneline', '--no-decorate', $before . '..' . $after])['out'] . "\n");
        }

        // A fresh process, so migrations run on the code that was just pulled.
        $this->stdout('$ php yii migrate/up --interactive=0' . "\n");
        $migrate = $this->yii(['migrate/up', '--interactive=0'], self::MIGRATE_TIMEOUT);
        $this->stdout($migrate['out'] . "\n");
        if ($migrate['code'] !== 0) {
            $this->stderr("migrate/up failed (exit {$migrate['code']}). The code is already pulled; fix the migration and run deploy/run again.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        // Schema caches (console and frontend) so new columns are seen at once.
        $this->yii(['cache/flush-schema', 'db', '--interactive=0']);
        $this->stdout("Schema cache flushed.\n");

        $this->stdout("Deployed " . substr($after, 0, 7) . ($after === $before ? ' (already up to date)' : '') . ".\n");

        return ExitCode::OK;
    }

    /** @return array{code:int, out:string} */
    private function git(array $args, int $timeout = 60): array
    {
        $bin = trim((string) (Yii::$app->params['console.gitBinary'] ?? '')) ?: 'git';

        return $this->exec(array_merge([$bin], $args), $timeout);
    }

    /** @return array{code:int, out:string} */
    private function yii(array $args, int $timeout = 120): array
    {
        $php = trim((string) (Yii::$app->params['console.phpBinary'] ?? '')) ?: PHP_BINARY;

        return $this->exec(array_merge([$php, 'yii'], $args), $timeout);
    }

    /**
     * Runs a command in the app root without a shell. When started from the admin (php-fpm) the
     * environment is thin, so HOME (for ~/.ssh and git config) and a usable PATH are filled in,
     * and git/ssh are told never to wait for a password prompt.
     *
     * @param list<string> $cmd
     * @return array{code:int, out:string}
     */
    private function exec(array $cmd, int $timeout): array
    {
        $env = getenv();
        if (empty($env['HOME']) && function_exists('posix_getpwuid')) {
            $env['HOME'] = (string) (posix_getpwuid(posix_geteuid())['dir'] ?? '');
        }
        $env['PATH'] = trim(($env['PATH'] ?? '') . ':' . (getenv('HOME') ?: ($env['HOME'] ?? '')) . '/bin:/usr/local/bin:/usr/bin:/bin', ':');
        $env['GIT_TERMINAL_PROMPT'] = '0';
        $env['GIT_SSH_COMMAND'] = 'ssh -o BatchMode=yes';

        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes, ConsoleRunner::rootDir(), $env);
        if (!is_resource($proc)) {
            return ['code' => 127, 'out' => 'Could not start: ' . implode(' ', $cmd)];
        }
        stream_set_blocking($pipes[1], false);
        $out = '';
        $deadline = time() + $timeout;
        while (true) {
            $out .= (string) stream_get_contents($pipes[1]);
            $status = proc_get_status($proc);
            if (!$status['running']) {
                $out .= (string) stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                proc_close($proc);
                return ['code' => (int) $status['exitcode'], 'out' => rtrim($out)];
            }
            if (time() > $deadline) {
                proc_terminate($proc);
                fclose($pipes[1]);
                proc_close($proc);
                return ['code' => 124, 'out' => rtrim($out) . "\nTimed out after {$timeout} s."];
            }
            usleep(100000);
        }
    }
}
