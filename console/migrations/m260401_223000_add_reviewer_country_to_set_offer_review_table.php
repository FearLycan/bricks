<?php

use yii\db\Migration;

class m260401_223000_add_reviewer_country_to_set_offer_review_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%set_offer_review}}', 'reviewer_country', $this->string(10)->null()->after('author_name'));
        $this->createIndex('{{%set_offer_review_reviewer_country_index}}', '{{%set_offer_review}}', 'reviewer_country');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%set_offer_review_reviewer_country_index}}', '{{%set_offer_review}}');
        $this->dropColumn('{{%set_offer_review}}', 'reviewer_country');
    }
}
