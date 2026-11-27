<?php

use yii\db\Migration;

/**
 * Old set slugs, so a set can get a new slug (Set::rebuildSlug) while every old URL
 * (Google, Pinterest pins, shared links) keeps working through a 301 to the current one.
 * Ported from store-base (product_slug_history).
 */
class m261127_230000_create_set_slug_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%set_slug_history}}', [
            'id'         => $this->primaryKey(),
            'set_id'     => $this->integer()->notNull(),
            'slug'       => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux-set_slug_history-slug', '{{%set_slug_history}}', 'slug', true);
        $this->createIndex('idx-set_slug_history-set', '{{%set_slug_history}}', 'set_id');
        $this->addForeignKey('fk-set_slug_history-set', '{{%set_slug_history}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%set_slug_history}}');
    }
}
