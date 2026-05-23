<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int         $id
 * @property string      $level
 * @property string|null $category
 * @property string|null $source
 * @property string|null $prefix
 * @property string      $message
 * @property string|null $trace
 * @property string|null $context
 * @property string      $created_at
 * @property string|null $resolved_at
 */
class Log extends ActiveRecord
{
    public const LEVEL_ERROR = 'error';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_INFO = 'info';

    public const SOURCE_CONSOLE = 'console';
    public const SOURCE_BACKEND = 'backend';
    public const SOURCE_FRONTEND = 'frontend';

    public static function tableName(): string
    {
        return '{{%log}}';
    }

    public function rules(): array
    {
        return [
            [['level', 'message'], 'required'],
            [['message', 'trace', 'context'], 'string'],
            [['created_at', 'resolved_at'], 'safe'],
            [['level', 'source'], 'string', 'max' => 20],
            [['category', 'prefix'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'          => 'ID',
            'level'       => 'Level',
            'category'    => 'Category',
            'source'      => 'Source',
            'prefix'      => 'Context',
            'message'     => 'Message',
            'trace'       => 'Stack trace',
            'context'     => 'Extra context',
            'created_at'  => 'Occurred at',
            'resolved_at' => 'Resolved at',
        ];
    }

    public static function levelOptions(): array
    {
        return [
            self::LEVEL_ERROR   => 'Error',
            self::LEVEL_WARNING => 'Warning',
            self::LEVEL_INFO    => 'Info',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_CONSOLE  => 'Console (cron)',
            self::SOURCE_BACKEND  => 'Backend (admin)',
            self::SOURCE_FRONTEND => 'Frontend',
        ];
    }

    public function getLevelLabel(): string
    {
        return self::levelOptions()[$this->level] ?? (string)$this->level;
    }

    public function getSourceLabel(): string
    {
        if ($this->source === null) {
            return '-';
        }

        return self::sourceOptions()[$this->source] ?? (string)$this->source;
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function getShortMessage(int $maxLength = 200): string
    {
        $message = (string)$this->message;
        if (mb_strlen($message) <= $maxLength) {
            return $message;
        }

        return mb_substr($message, 0, $maxLength) . '…';
    }
}
