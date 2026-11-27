<?php

declare(strict_types=1);

namespace console\controllers;

use common\components\ConsoleRunner;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Runs the tasks from console/config/schedule.php. One crontab line calls `schedule/run`
 * every minute; it starts each task that is due as a background process, so a long task
 * never delays the others. Ported from store-base.
 *
 *   php yii schedule/run          start due tasks (from cron, every minute)
 *   php yii schedule/list         show tasks, last start and whether one is running
 *   php yii schedule/exec <name>  run one task now, with its lock and log
 */
final class ScheduleController extends Controller
{
    public function actionRun(): int
    {
        // Optional shift of the whole timetable (params-local `schedule.offsetMinutes`), so this
        // site's heavy jobs do not start together with other sites on the same server.
        $now = time() - 60 * (int) (Yii::$app->params['schedule.offsetMinutes'] ?? 0);
        foreach ($this->tasks() as $name => [$expr]) {
            if (!self::isDue($expr, $now)) {
                continue;
            }
            $log = Yii::getAlias('@console/runtime/logs/schedule-' . $name . '.log');
            $cmd = sprintf(
                'cd %s && %s yii schedule/exec %s >> %s 2>&1 &',
                escapeshellarg(ConsoleRunner::rootDir()),
                escapeshellarg(PHP_BINARY),
                escapeshellarg($name),
                escapeshellarg($log),
            );
            exec($cmd);
            $this->stdout(date('Y-m-d H:i') . " started {$name}\n");
        }

        return ExitCode::OK;
    }

    /** Runs one task in this process, holding a file lock so runs never overlap. */
    public function actionExec(string $name): int
    {
        $tasks = $this->tasks();
        if (!isset($tasks[$name])) {
            $this->stderr("Unknown task: {$name}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $lock = fopen(Yii::getAlias('@console/runtime/schedule-' . $name . '.lock'), 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            $this->stdout(date('Y-m-d H:i:s') . " {$name}: previous run still going, skipped\n");
            return ExitCode::OK;
        }
        [$expr, $route] = $tasks[$name];
        $parts = preg_split('/\s+/', trim($route));
        $action = array_shift($parts);
        $this->stdout(date('Y-m-d H:i:s') . " {$name}: {$route}\n");
        @touch(Yii::getAlias('@console/runtime/schedule-' . $name . '.last'));
        try {
            $code = Yii::$app->runAction($action, $parts);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return is_int($code) ? $code : ExitCode::OK;
    }

    public function actionList(): int
    {
        foreach ($this->tasks() as $name => [$expr, $route]) {
            $last = Yii::getAlias('@console/runtime/schedule-' . $name . '.last');
            $lock = fopen(Yii::getAlias('@console/runtime/schedule-' . $name . '.lock'), 'c');
            $running = $lock !== false && !flock($lock, LOCK_EX | LOCK_NB);
            if ($lock !== false) {
                fclose($lock);
            }
            $this->stdout(sprintf(
                "%-12s %-15s %-38s last start: %s%s\n",
                $name,
                $expr,
                $route,
                is_file($last) ? date('Y-m-d H:i', (int) filemtime($last)) : 'never',
                $running ? '  [running]' : '',
            ));
        }

        return ExitCode::OK;
    }

    /** Trims *.log over 5 MB in console/ and frontend/runtime/logs to their last 5000 lines. */
    public function actionTrimLogs(int $maxMb = 5, int $keepLines = 5000): int
    {
        $files = array_merge(
            glob(Yii::getAlias('@console/runtime/logs/*.log')) ?: [],
            glob(Yii::getAlias('@frontend/runtime/logs/*.log')) ?: [],
        );
        foreach ($files as $file) {
            if (filesize($file) <= $maxMb * 1024 * 1024) {
                continue;
            }
            $lines = new \SplFileObject($file);
            $lines->seek(PHP_INT_MAX);
            $total = $lines->key();
            $keep = [];
            $lines->seek(max(0, $total - $keepLines));
            while (!$lines->eof()) {
                $keep[] = $lines->current();
                $lines->next();
            }
            file_put_contents($file, implode('', $keep));
            $this->stdout("trimmed {$file}\n");
        }

        return ExitCode::OK;
    }

    /** @return array<string, array{0:string,1:string}> */
    private function tasks(): array
    {
        return require Yii::getAlias('@console/config/schedule.php');
    }

    /**
     * Standard 5-field cron match: minute hour day-of-month month day-of-week, with
     * *, lists (1,15), ranges (1-5), steps (*\/5, 0-30/10). Day-of-week 0 or 7 = Sunday.
     * As in cron, when both day fields are restricted either may match.
     */
    public static function isDue(string $expr, int $ts): bool
    {
        $f = preg_split('/\s+/', trim($expr));
        if (count($f) !== 5) {
            return false;
        }
        [$min, $hour, $dom, $mon, $dow] = $f;
        $t = getdate($ts);
        $wday = $t['wday'];
        if (!self::field($min, $t['minutes'], 0, 59) || !self::field($hour, $t['hours'], 0, 23) || !self::field($mon, $t['mon'], 1, 12)) {
            return false;
        }
        $domOk = self::field($dom, $t['mday'], 1, 31);
        $dowOk = self::field($dow, $wday, 0, 7) || ($wday === 0 && self::field($dow, 7, 0, 7));
        if ($dom !== '*' && $dow !== '*') {
            return $domOk || $dowOk;
        }

        return $domOk && $dowOk;
    }

    private static function field(string $spec, int $value, int $min, int $max): bool
    {
        foreach (explode(',', $spec) as $part) {
            $step = 1;
            if (str_contains($part, '/')) {
                [$part, $s] = explode('/', $part, 2);
                $step = max(1, (int) $s);
            }
            if ($part === '*') {
                [$lo, $hi] = [$min, $max];
            } elseif (str_contains($part, '-')) {
                [$lo, $hi] = array_map('intval', explode('-', $part, 2));
            } else {
                $lo = $hi = (int) $part;
                if ($step > 1) {
                    $hi = $max;
                }
            }
            if ($value >= $lo && $value <= $hi && ($value - $lo) % $step === 0) {
                return true;
            }
        }

        return false;
    }
}
