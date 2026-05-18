<?php

use yii\db\Migration;

/**
 * The initial create migration ran on a MySQL server whose default charset is latin1,
 * which silently produced tables that reject non-ASCII review content (e.g. Polish
 * characters like ł). This migration converts those tables to utf8mb4.
 */
class m260518_140000_convert_set_review_tables_to_utf8mb4 extends Migration
{
    private const TABLES = [
        '{{%set_review}}',
        '{{%set_review_score}}',
        '{{%set_review_answer}}',
        '{{%user_preference}}',
    ];

    public function safeUp(): void
    {
        if ($this->db->driverName !== 'mysql') {
            return;
        }

        foreach (self::TABLES as $table) {
            $this->execute("ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function safeDown(): void
    {
        if ($this->db->driverName !== 'mysql') {
            return;
        }

        foreach (array_reverse(self::TABLES) as $table) {
            $this->execute("ALTER TABLE $table CONVERT TO CHARACTER SET latin1");
        }
    }
}
