<?php

use yii\db\Migration;

class m260523_120000_create_log_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%log}}', [
            'id'          => $this->primaryKey(),
            'level'       => $this->string(20)->notNull(),
            'category'    => $this->string(255)->null(),
            'source'      => $this->string(20)->null(),
            'prefix'      => $this->string(255)->null(),
            'message'     => $this->text()->notNull(),
            'trace'       => $this->getDb()->getSchema()->createColumnSchemaBuilder('MEDIUMTEXT')->null(),
            'context'     => $this->text()->null(),
            'created_at'  => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'resolved_at' => $this->timestamp()->null(),
        ], $tableOptions);

        $this->createIndex('{{%log_level_index}}', '{{%log}}', 'level');
        $this->createIndex('{{%log_category_index}}', '{{%log}}', 'category');
        $this->createIndex('{{%log_source_index}}', '{{%log}}', 'source');
        $this->createIndex('{{%log_created_at_index}}', '{{%log}}', 'created_at');
        $this->createIndex('{{%log_resolved_at_index}}', '{{%log}}', 'resolved_at');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%log}}');
    }
}
