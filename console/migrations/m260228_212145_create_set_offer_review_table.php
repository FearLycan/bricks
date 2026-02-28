<?php

use yii\db\Migration;

class m260228_212145_create_set_offer_review_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%set_offer_review}}', [
            'id'                 => $this->primaryKey(),
            'set_offer_id'       => $this->integer()->notNull(),
            'external_review_id' => $this->string(100)->null(),
            'author_name'        => $this->string()->null(),
            'title'              => $this->string()->null(),
            'content'            => $this->text()->null(),
            'rating_value'       => $this->float(4, 2)->null(),
            'rating_scale_max'   => $this->float(4, 2)->null()->defaultValue(5.0),
            'reviewed_at'        => $this->dateTime()->null(),
            'source'             => $this->string(50)->null(),
            'is_manual_override' => $this->boolean()->notNull()->defaultValue(0),
            'created_at'         => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'         => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_offer_review_offer_id_fk}}', '{{%set_offer_review}}', 'set_offer_id', '{{%set_offer}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_offer_review_offer_id_index}}', '{{%set_offer_review}}', 'set_offer_id');
        $this->createIndex('{{%set_offer_review_rating_value_index}}', '{{%set_offer_review}}', 'rating_value');
        $this->createIndex('{{%set_offer_review_reviewed_at_index}}', '{{%set_offer_review}}', 'reviewed_at');
        $this->createIndex('{{%set_offer_review_source_index}}', '{{%set_offer_review}}', 'source');
        $this->createIndex('{{%set_offer_review_offer_external_uindex}}', '{{%set_offer_review}}', ['set_offer_id', 'external_review_id'], true);
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%set_offer_review_offer_id_fk}}', '{{%set_offer_review}}');

        $this->dropIndex('{{%set_offer_review_offer_id_index}}', '{{%set_offer_review}}');
        $this->dropIndex('{{%set_offer_review_rating_value_index}}', '{{%set_offer_review}}');
        $this->dropIndex('{{%set_offer_review_reviewed_at_index}}', '{{%set_offer_review}}');
        $this->dropIndex('{{%set_offer_review_source_index}}', '{{%set_offer_review}}');
        $this->dropIndex('{{%set_offer_review_offer_external_uindex}}', '{{%set_offer_review}}');

        $this->dropTable('{{%set_offer_review}}');
    }
}
