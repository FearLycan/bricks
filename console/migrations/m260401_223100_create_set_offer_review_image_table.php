<?php

use yii\db\Migration;

class m260401_223100_create_set_offer_review_image_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%set_offer_review_image}}', [
            'id'                  => $this->primaryKey(),
            'set_offer_review_id' => $this->integer()->notNull(),
            'url'                 => $this->string()->notNull(),
            'position'            => $this->integer()->notNull()->defaultValue(0),
            'created_at'          => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'          => $this->timestamp()->null(),
        ]);

        $this->addForeignKey(
            '{{%set_offer_review_image_review_id_fk}}',
            '{{%set_offer_review_image}}',
            'set_offer_review_id',
            '{{%set_offer_review}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('{{%set_offer_review_image_review_id_index}}', '{{%set_offer_review_image}}', 'set_offer_review_id');
        $this->createIndex('{{%set_offer_review_image_position_index}}', '{{%set_offer_review_image}}', 'position');
        $this->createIndex('{{%set_offer_review_image_review_url_uindex}}', '{{%set_offer_review_image}}', ['set_offer_review_id', 'url'], true);
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%set_offer_review_image_review_id_fk}}', '{{%set_offer_review_image}}');

        $this->dropIndex('{{%set_offer_review_image_review_id_index}}', '{{%set_offer_review_image}}');
        $this->dropIndex('{{%set_offer_review_image_position_index}}', '{{%set_offer_review_image}}');
        $this->dropIndex('{{%set_offer_review_image_review_url_uindex}}', '{{%set_offer_review_image}}');

        $this->dropTable('{{%set_offer_review_image}}');
    }
}
