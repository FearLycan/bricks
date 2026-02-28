<?php

use yii\db\Migration;

class m260228_212143_create_store_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%store}}', [
            'id'         => $this->primaryKey(),
            'code'       => $this->string(50)->notNull(),
            'name'       => $this->string()->notNull(),
            'url'        => $this->string()->null(),
            'logo'       => $this->string()->null(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->createIndex('{{%store_code_uindex}}', '{{%store}}', 'code', true);
        $this->createIndex('{{%store_name_index}}', '{{%store}}', 'name');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%store_code_uindex}}', '{{%store}}');
        $this->dropIndex('{{%store_name_index}}', '{{%store}}');

        $this->dropTable('{{%store}}');
    }
}
