<?php

use yii\db\Migration;

class m260513_190000_create_owned_set_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%owned_set}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->notNull(),
            'set_id'     => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_owned_set_user_set', '{{%owned_set}}', ['user_id', 'set_id'], true);
        $this->createIndex('idx_owned_set_user', '{{%owned_set}}', 'user_id');
        $this->createIndex('idx_owned_set_set', '{{%owned_set}}', 'set_id');

        $this->addForeignKey(
            'fk_owned_set_user',
            '{{%owned_set}}', 'user_id',
            '{{%user}}', 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            'fk_owned_set_set',
            '{{%owned_set}}', 'set_id',
            '{{%set}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_owned_set_set', '{{%owned_set}}');
        $this->dropForeignKey('fk_owned_set_user', '{{%owned_set}}');
        $this->dropTable('{{%owned_set}}');
    }
}
