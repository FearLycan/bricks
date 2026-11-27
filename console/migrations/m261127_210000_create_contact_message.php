<?php

use yii\db\Migration;

/**
 * Messages from the contact form, kept so none are lost when mail fails
 * (read at /management/messages). Ported from store-base.
 */
class m261127_210000_create_contact_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%contact_message}}', [
            'id'         => $this->primaryKey(),
            'name'       => $this->string(120)->notNull(),
            'email'      => $this->string(190)->notNull(),
            'subject'    => $this->string(200)->notNull(),
            'body'       => $this->text()->notNull(),
            'lang'       => $this->string(5)->null(),
            'ip_hash'    => $this->string(64)->null(),
            'read_at'    => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-contact_message-read', '{{%contact_message}}', ['read_at', 'created_at']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%contact_message}}');
    }
}
