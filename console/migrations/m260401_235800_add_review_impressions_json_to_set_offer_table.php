<?php

use yii\db\Migration;

class m260401_235800_add_review_impressions_json_to_set_offer_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%set_offer}}', 'review_impressions', $this->text()->null()->after('review_count'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%set_offer}}', 'review_impressions');
    }
}
