<?php

use yii\db\Migration;

/**
 * Handles adding availability to table `{{%set}}`.
 */
class m260221_194544_add_availability_to_set_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'availability', $this->string()->null()->after('dimensions'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%set}}', 'availability');
    }
}
