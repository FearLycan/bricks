<?php

use yii\db\Migration;

/**
 * Handles the creation of tables `{{%tag}}` and `{{%set_tag}}`.
 */
class m260226_213058_create_tag_and_set_tag_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tag}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'slug' => $this->string()->null(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->createIndex('{{%tag_name_uidx}}', '{{%tag}}', 'name', true);
        $this->createIndex('{{%tag_slug_index}}', '{{%tag}}', 'slug');

        $this->createTable('{{%set_tag}}', [
            'id' => $this->primaryKey(),
            'set_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_tag_set_id_fk}}', '{{%set_tag}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('{{%set_tag_tag_id_fk}}', '{{%set_tag}}', 'tag_id', '{{%tag}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_tag_set_id_index}}', '{{%set_tag}}', 'set_id');
        $this->createIndex('{{%set_tag_tag_id_index}}', '{{%set_tag}}', 'tag_id');
        $this->createIndex('{{%set_tag_set_id_tag_id_uidx}}', '{{%set_tag}}', ['set_id', 'tag_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%set_tag}}');
        $this->dropTable('{{%tag}}');
    }
}
