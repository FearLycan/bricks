<?php

use yii\db\Migration;

/**
 * Handles adding description to table `{{%set}}`.
 */
class m260226_212821_add_description_and_tags_to_set_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'description', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%set}}', 'description');
    }
}
