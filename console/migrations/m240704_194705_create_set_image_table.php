<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%set_image}}`.
 */
class m240704_194705_create_set_image_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%set_image}}', [
            'id'         => $this->primaryKey(),
            'url'        => $this->string()->notNull(),
            'set_id'     => $this->integer()->notNull(),
            'type'       => $this->string(30)->notNull(),
            'status'     => $this->smallInteger()->defaultValue(1),
            'kind'       => $this->string(30),
            'hash'       => $this->string(50),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_image_set_id_fk}}', '{{%set_image}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_image_type_index}}', '{{%set_image}}', 'type');
        $this->createIndex('{{%set_image_kind_index}}', '{{%set_image}}', 'kind');
        $this->createIndex('{{%set_image_status_index}}', '{{%set_image}}', 'status');
        $this->createIndex('{{%set_image_hash_index}}', '{{%set_image}}', 'hash');

        $this->createIndex('{{%set_image_created_at_index}}', '{{%set_image}}', 'created_at');
        $this->createIndex('{{%set_image_updated_at_index}}', '{{%set_image}}', 'updated_at');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%set_image}}');
    }
}
