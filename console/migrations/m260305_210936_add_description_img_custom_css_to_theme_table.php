<?php

use yii\db\Migration;

class m260305_210936_add_description_img_custom_css_to_theme_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%theme}}', 'description', $this->text()->null()->after('slug'));
        $this->addColumn('{{%theme}}', 'img', $this->string()->null()->after('description'));
        $this->addColumn('{{%theme}}', 'custom_css', $this->text()->null()->after('img'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%theme}}', 'custom_css');
        $this->dropColumn('{{%theme}}', 'img');
        $this->dropColumn('{{%theme}}', 'description');
    }
}
