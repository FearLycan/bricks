<?php

use yii\db\Migration;

class m260514_120000_add_preferred_language_to_user_settings extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%user_settings}}', 'preferred_language', $this->string(5)->null()->after('hide_owned_sets'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%user_settings}}', 'preferred_language');
    }
}
