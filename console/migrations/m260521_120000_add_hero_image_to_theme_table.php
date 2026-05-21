<?php

use yii\db\Migration;

/**
 * Handles adding column `hero_image` to table `{{%theme}}`.
 */
class m260521_120000_add_hero_image_to_theme_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%theme}}', 'hero_image', $this->string(255)->null()->after('img'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%theme}}', 'hero_image');
    }
}
