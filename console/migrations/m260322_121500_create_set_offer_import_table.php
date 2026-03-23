<?php

use yii\db\Migration;

class m260322_121500_create_set_offer_import_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%set_offer_import}}', [
            'id' => $this->primaryKey(),
            'set_id' => $this->integer()->notNull(),
            'input_url' => $this->string()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'error_message' => $this->string(1000)->null(),
            'attempts' => $this->smallInteger()->notNull()->defaultValue(0),
            'set_offer_id' => $this->integer()->null(),
            'processed_at' => $this->dateTime()->null(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_offer_import_set_id_fk}}', '{{%set_offer_import}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('{{%set_offer_import_set_offer_id_fk}}', '{{%set_offer_import}}', 'set_offer_id', '{{%set_offer}}', 'id', 'SET NULL', 'CASCADE');

        $this->createIndex('{{%set_offer_import_status_id_idx}}', '{{%set_offer_import}}', ['status', 'id']);
        $this->createIndex('{{%set_offer_import_set_id_idx}}', '{{%set_offer_import}}', 'set_id');
        $this->createIndex('{{%set_offer_import_set_offer_id_idx}}', '{{%set_offer_import}}', 'set_offer_id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%set_offer_import_set_id_fk}}', '{{%set_offer_import}}');
        $this->dropForeignKey('{{%set_offer_import_set_offer_id_fk}}', '{{%set_offer_import}}');

        $this->dropIndex('{{%set_offer_import_status_id_idx}}', '{{%set_offer_import}}');
        $this->dropIndex('{{%set_offer_import_set_id_idx}}', '{{%set_offer_import}}');
        $this->dropIndex('{{%set_offer_import_set_offer_id_idx}}', '{{%set_offer_import}}');

        $this->dropTable('{{%set_offer_import}}');
    }
}
