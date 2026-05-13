<?php

use yii\db\Migration;

class m260513_180000_create_wishlist_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%wishlist}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->notNull(),
            'set_id'     => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_wishlist_user_set', '{{%wishlist}}', ['user_id', 'set_id'], true);
        $this->createIndex('idx_wishlist_user', '{{%wishlist}}', 'user_id');
        $this->createIndex('idx_wishlist_set', '{{%wishlist}}', 'set_id');

        $this->addForeignKey(
            'fk_wishlist_user',
            '{{%wishlist}}', 'user_id',
            '{{%user}}', 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            'fk_wishlist_set',
            '{{%wishlist}}', 'set_id',
            '{{%set}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_wishlist_set', '{{%wishlist}}');
        $this->dropForeignKey('fk_wishlist_user', '{{%wishlist}}');
        $this->dropTable('{{%wishlist}}');
    }
}
