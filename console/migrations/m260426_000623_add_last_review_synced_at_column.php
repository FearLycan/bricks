<?php

use yii\db\Migration;

class m260426_000623_add_last_review_synced_at_column extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%set_offer}}', 'last_review_synced_at', $this->timestamp()->null()->after('is_manual_override'));
        $this->createIndex('{{%set_offer_last_review_synced_at_idx}}', '{{%set_offer}}', 'last_review_synced_at');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%set_offer_last_review_synced_at_idx}}', '{{%set_offer}}');
        $this->dropColumn('{{%set_offer}}', 'last_review_synced_at');
    }
}
