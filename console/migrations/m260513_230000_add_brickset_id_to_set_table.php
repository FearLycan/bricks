<?php

use yii\db\Migration;

class m260513_230000_add_brickset_id_to_set_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'brickset_id', $this->integer()->null()->after('brickset_url'));
        $this->createIndex('{{%set_brickset_id_idx}}', '{{%set}}', 'brickset_id');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%set_brickset_id_idx}}', '{{%set}}');
        $this->dropColumn('{{%set}}', 'brickset_id');
    }
}
