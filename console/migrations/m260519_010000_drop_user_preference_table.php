<?php

use yii\db\Migration;

/**
 * Removes the user_preference table. Preferences that were originally global per-user
 * (set_purpose, priority) are now stored per-review in set_review_answer instead. For
 * future per-user options use the user_settings table with explicit columns.
 */
class m260519_010000_drop_user_preference_table extends Migration
{
    public function safeUp(): void
    {
        $this->dropForeignKey('{{%user_preference_user_id_fk}}', '{{%user_preference}}');
        $this->dropTable('{{%user_preference}}');
    }

    public function safeDown(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_preference}}', [
            'id'               => $this->primaryKey(),
            'user_id'          => $this->integer()->notNull(),
            'preference_key'   => $this->string(60)->notNull(),
            'preference_value' => $this->string(120)->notNull(),
            'created_at'       => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'       => $this->timestamp()->null(),
        ], $tableOptions);

        $this->addForeignKey('{{%user_preference_user_id_fk}}', '{{%user_preference}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%user_preference_user_key_value_uindex}}', '{{%user_preference}}', ['user_id', 'preference_key', 'preference_value'], true);
        $this->createIndex('{{%user_preference_key_value_index}}', '{{%user_preference}}', ['preference_key', 'preference_value']);
    }
}
