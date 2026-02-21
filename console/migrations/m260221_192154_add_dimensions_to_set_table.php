<?php

use yii\db\Migration;

/**
 * Handles adding dimensions to table `{{%set}}`.
 */
class m260221_192154_add_dimensions_to_set_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'dimensions', $this->string()->null()->after('age'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%set}}', 'dimensions');
    }
}
