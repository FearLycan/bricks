<?php

use yii\db\Migration;

class m260325_121000_add_offer_discovery_checked_at_to_set_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%set}}', 'offer_discovery_checked_at', $this->dateTime()->null()->after('updated_at'));
        $this->createIndex('{{%set_offer_discovery_checked_at_idx}}', '{{%set}}', 'offer_discovery_checked_at');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%set_offer_discovery_checked_at_idx}}', '{{%set}}');
        $this->dropColumn('{{%set}}', 'offer_discovery_checked_at');
    }
}
