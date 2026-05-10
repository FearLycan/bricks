<?php

use yii\db\Migration;

class m260510_130000_create_search_wizard_hash_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%search_wizard_hash}}', [
            'id'         => $this->primaryKey(),
            'hash'       => $this->string(10)->notNull(),
            'user_id'    => $this->integer()->null(),
            'answers'    => $this->text()->null(),
            'filters'    => $this->text()->null(),
            'labels'     => $this->text()->null(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->createIndex('idx_wizard_hash_hash', '{{%search_wizard_hash}}', 'hash', true);
        $this->createIndex('idx_wizard_hash_user', '{{%search_wizard_hash}}', 'user_id');

        $this->addForeignKey(
            'fk_wizard_hash_user',
            '{{%search_wizard_hash}}', 'user_id',
            '{{%user}}', 'id',
            'SET NULL', 'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_wizard_hash_user', '{{%search_wizard_hash}}');
        $this->dropTable('{{%search_wizard_hash}}');
    }
}
