<?php

use yii\db\Migration;

class m260228_212144_create_set_offer_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%set_offer}}', [
            'id'                 => $this->primaryKey(),
            'set_id'             => $this->integer()->notNull(),
            'store_id'           => $this->integer()->notNull(),
            'external_id'        => $this->string(100)->null(),
            'name'               => $this->string()->null(),
            'url'                => $this->string()->null(),
            'image'              => $this->string()->null(),
            'currency_code'      => $this->string(3)->notNull()->defaultValue('USD'),
            'price'              => $this->integer()->null(),
            'availability'       => $this->string()->null(),
            'rating_value'       => $this->float(4, 2)->null(),
            'rating_scale_max'   => $this->float(4, 2)->null()->defaultValue(5.0),
            'review_count'       => $this->integer()->notNull()->defaultValue(0),
            'source'             => $this->string(50)->null(),
            'is_manual_override' => $this->boolean()->notNull()->defaultValue(0),
            'synced_at'          => $this->dateTime()->null(),
            'created_at'         => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'         => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_offer_set_id_fk}}', '{{%set_offer}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('{{%set_offer_store_id_fk}}', '{{%set_offer}}', 'store_id', '{{%store}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createIndex('{{%set_offer_set_id_index}}', '{{%set_offer}}', 'set_id');
        $this->createIndex('{{%set_offer_store_id_index}}', '{{%set_offer}}', 'store_id');
        $this->createIndex('{{%set_offer_price_index}}', '{{%set_offer}}', 'price');
        $this->createIndex('{{%set_offer_rating_value_index}}', '{{%set_offer}}', 'rating_value');
        $this->createIndex('{{%set_offer_source_index}}', '{{%set_offer}}', 'source');
        $this->createIndex('{{%set_offer_set_store_external_uindex}}', '{{%set_offer}}', ['set_id', 'store_id', 'external_id'], true);
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%set_offer_set_id_fk}}', '{{%set_offer}}');
        $this->dropForeignKey('{{%set_offer_store_id_fk}}', '{{%set_offer}}');

        $this->dropIndex('{{%set_offer_set_id_index}}', '{{%set_offer}}');
        $this->dropIndex('{{%set_offer_store_id_index}}', '{{%set_offer}}');
        $this->dropIndex('{{%set_offer_price_index}}', '{{%set_offer}}');
        $this->dropIndex('{{%set_offer_rating_value_index}}', '{{%set_offer}}');
        $this->dropIndex('{{%set_offer_source_index}}', '{{%set_offer}}');
        $this->dropIndex('{{%set_offer_set_store_external_uindex}}', '{{%set_offer}}');

        $this->dropTable('{{%set_offer}}');
    }
}
