<?php

use yii\db\Migration;

class m260513_230100_create_set_instruction_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%set_instruction}}', [
            'id'          => $this->primaryKey(),
            'set_id'      => $this->integer()->notNull(),
            'url'         => $this->string(500)->notNull(),
            'description' => $this->string()->null(),
            'sort_order'  => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at'  => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'  => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_instruction_set_id_fk}}', '{{%set_instruction}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_instruction_set_id_idx}}', '{{%set_instruction}}', 'set_id');
        $this->createIndex('{{%set_instruction_set_url_uniq}}', '{{%set_instruction}}', ['set_id', 'url'], true);
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%set_instruction_set_id_fk}}', '{{%set_instruction}}');
        $this->dropTable('{{%set_instruction}}');
    }
}
