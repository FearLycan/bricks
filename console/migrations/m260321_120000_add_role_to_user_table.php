<?php

use yii\db\Migration;

class m260321_120000_add_role_to_user_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'role', $this->string(32)->notNull()->defaultValue('user')->after('email'));
        $this->createIndex('idx-user-role', '{{%user}}', 'role');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-user-role', '{{%user}}');
        $this->dropColumn('{{%user}}', 'role');
    }
}
