<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%set_price}}`.
 */
class m260221_145331_create_set_price_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%set_price}}', [
            'id'                   => $this->primaryKey(),
            'set_id'               => $this->integer()->notNull(),
            'country_code'         => $this->string(2)->notNull(),
            'retail_price_cents'   => $this->integer()->notNull(),
            'date_first_available' => $this->timestamp()->null(),
            'date_last_available'  => $this->timestamp()->null(),
            'created_at'           => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'           => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_price_set_id_fk}}', '{{%set_price}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%set_price_set_id_fk}}', '{{%set_price}}');
        $this->dropTable('{{%set_price}}');
    }
}
