<?php

namespace common\components\log;

use Throwable;
use Yii;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;

class DbLogTarget extends Target
{
    public string $db = 'db';
    public string $table = '{{%log}}';
    public ?string $source = null;

    private bool $isWriting = false;

    public function init(): void
    {
        parent::init();

        if ($this->source === null) {
            $this->source = $this->detectSource();
        }
    }

    public function export(): void
    {
        if ($this->isWriting) {
            return;
        }

        $this->isWriting = true;
        try {
            $db = Yii::$app->get($this->db, false);
            if ($db === null) {
                return;
            }

            $command = $db->createCommand();
            $now = date('Y-m-d H:i:s');

            foreach ($this->messages as $message) {
                $row = $this->buildRow($message, $now);
                if ($row === null) {
                    continue;
                }

                try {
                    $command->insert($this->table, $row)->execute();
                } catch (Throwable $exception) {
                    error_log('DbLogTarget insert failed: ' . $exception->getMessage());
                }
            }
        } finally {
            $this->isWriting = false;
        }
    }

    private function buildRow(array $message, string $now): ?array
    {
        [$text, $level, $category, $timestamp, $traceFrames] = array_pad($message, 5, null);

        $levelLabel = $this->normalizeLevel((int)$level);
        if ($levelLabel === null) {
            return null;
        }

        $messageString = $this->stringifyMessage($text);
        if ($messageString === '') {
            return null;
        }

        $traceString = null;
        if ($text instanceof Throwable) {
            $traceString = $text->getTraceAsString();
        } elseif (is_array($traceFrames) && $traceFrames !== []) {
            $lines = [];
            foreach ($traceFrames as $frame) {
                if (isset($frame['file'], $frame['line'])) {
                    $lines[] = "in {$frame['file']}:{$frame['line']}";
                }
            }
            $traceString = implode("\n", $lines) ?: null;
        }

        $prefix = $this->getMessagePrefix($message);
        $createdAt = $timestamp !== null ? date('Y-m-d H:i:s', (int)$timestamp) : $now;

        return [
            'level'      => $levelLabel,
            'category'   => $category !== null ? mb_substr((string)$category, 0, 255) : null,
            'source'     => $this->source,
            'prefix'     => $prefix !== '' ? mb_substr($prefix, 0, 255) : null,
            'message'    => mb_substr($messageString, 0, 65000),
            'trace'      => $traceString !== null ? mb_substr($traceString, 0, 1000000) : null,
            'context'    => null,
            'created_at' => $createdAt,
        ];
    }

    private function normalizeLevel(int $level): ?string
    {
        return match ($level) {
            Logger::LEVEL_ERROR   => 'error',
            Logger::LEVEL_WARNING => 'warning',
            Logger::LEVEL_INFO    => 'info',
            default               => null,
        };
    }

    private function stringifyMessage(mixed $text): string
    {
        if (is_string($text)) {
            return $text;
        }

        if ($text instanceof Throwable) {
            return get_class($text) . ': ' . $text->getMessage()
                . ' in ' . $text->getFile() . ':' . $text->getLine();
        }

        return VarDumper::export($text);
    }

    private function detectSource(): string
    {
        $appId = Yii::$app->id ?? '';
        if (str_contains($appId, 'console')) {
            return 'console';
        }
        if (str_contains($appId, 'backend')) {
            return 'backend';
        }
        if (str_contains($appId, 'frontend')) {
            return 'frontend';
        }

        return 'unknown';
    }
}
