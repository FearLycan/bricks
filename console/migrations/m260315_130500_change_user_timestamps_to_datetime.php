<?php

use yii\db\Migration;

class m260315_130500_change_user_timestamps_to_datetime extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%user}}', 'created_at');
        $this->dropColumn('{{%user}}', 'updated_at');
        $this->addColumn('{{%user}}', 'created_at', $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->addColumn('{{%user}}', 'updated_at', $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'created_at');
        $this->dropColumn('{{%user}}', 'updated_at');
        $this->addColumn('{{%user}}', 'created_at', $this->integer()->notNull());
        $this->addColumn('{{%user}}', 'updated_at', $this->integer()->notNull());
    }
}
