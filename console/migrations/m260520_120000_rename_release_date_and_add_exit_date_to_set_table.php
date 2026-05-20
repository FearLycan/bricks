<?php

use yii\db\Migration;

class m260520_120000_rename_release_date_and_add_exit_date_to_set_table extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('{{%set_release_date_idx}}', '{{%set}}');
        $this->renameColumn('{{%set}}', 'release_date', 'launch_date');
        $this->createIndex('{{%set_launch_date_idx}}', '{{%set}}', 'launch_date');

        $this->addColumn('{{%set}}', 'exit_date', $this->date()->null()->after('launch_date'));
        $this->createIndex('{{%set_exit_date_idx}}', '{{%set}}', 'exit_date');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%set_exit_date_idx}}', '{{%set}}');
        $this->dropColumn('{{%set}}', 'exit_date');

        $this->dropIndex('{{%set_launch_date_idx}}', '{{%set}}');
        $this->renameColumn('{{%set}}', 'launch_date', 'release_date');
        $this->createIndex('{{%set_release_date_idx}}', '{{%set}}', 'release_date');
    }
}
