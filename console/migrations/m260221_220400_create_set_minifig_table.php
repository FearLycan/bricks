<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%set_minifig}}`.
 */
class m260221_220400_create_set_minifig_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%set_minifig}}', [
            'id'             => $this->primaryKey(),
            'set_id'         => $this->integer()->notNull(),
            'rebrickable_id' => $this->integer()->notNull(),
            'number'         => $this->string(30)->notNull(),
            'name'           => $this->string()->notNull(),
            'quantity'       => $this->integer()->notNull()->defaultValue(1),
            'image'          => $this->string()->null(),
            'created_at'     => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'     => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_minifig_set_id_fk}}', '{{%set_minifig}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_minifig_set_id_index}}', '{{%set_minifig}}', 'set_id');
        $this->createIndex('{{%set_minifig_rebrickable_id_index}}', '{{%set_minifig}}', 'rebrickable_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%set_minifig_set_id_fk}}', '{{%set_minifig}}');
        $this->dropIndex('{{%set_minifig_set_id_index}}', '{{%set_minifig}}');
        $this->dropIndex('{{%set_minifig_rebrickable_id_index}}', '{{%set_minifig}}');
        $this->dropTable('{{%set_minifig}}');
    }
}
