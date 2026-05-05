<?php

use yii\db\Migration;

class m260505_120000_add_release_date_to_set_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'release_date', $this->date()->null()->after('released'));
        $this->createIndex('{{%set_release_date_idx}}', '{{%set}}', 'release_date');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%set_release_date_idx}}', '{{%set}}');
        $this->dropColumn('{{%set}}', 'release_date');
    }
}
