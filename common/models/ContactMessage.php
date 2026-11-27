<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * A message sent through the contact form (/site/contact).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $body
 * @property string|null $lang
 * @property string|null $ip_hash
 * @property int|null $read_at
 * @property int $created_at
 */
class ContactMessage extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%contact_message}}';
    }

    public static function unreadCount(): int
    {
        return (int)self::find()->where(['read_at' => null])->count();
    }
}
