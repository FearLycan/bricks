<?php

use yii\db\Migration;

class m260513_220000_create_user_settings_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%user_settings}}', [
            'user_id'         => $this->integer()->notNull(),
            'hide_owned_sets' => $this->boolean()->notNull()->defaultValue(false),
            'created_at'      => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'      => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY (user_id)',
        ]);

        $this->addForeignKey(
            'fk_user_settings_user',
            '{{%user_settings}}', 'user_id',
            '{{%user}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_user_settings_user', '{{%user_settings}}');
        $this->dropTable('{{%user_settings}}');
    }
}
