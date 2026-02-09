<?php

use yii\db\Migration;

/**
 * Handles adding subtheme_id to table `{{%set}}`.
 */
class m260210_004423_add_subtheme_id_to_set_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'subtheme_id', $this->integer()->null());
        $this->addForeignKey('{{%set_subtheme_id_fk}}', '{{%set}}', 'subtheme_id', '{{%theme}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%set_subtheme_id_fk}}', '{{%set}}');
        $this->dropColumn('{{%set}}', 'subtheme_id');
    }
}
