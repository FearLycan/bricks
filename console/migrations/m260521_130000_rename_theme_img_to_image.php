<?php

use yii\db\Migration;

/**
 * Handles renaming column `img` to `image` in table `{{%theme}}`.
 */
class m260521_130000_rename_theme_img_to_image extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%theme}}', 'img', 'image');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%theme}}', 'image', 'img');
    }
}
