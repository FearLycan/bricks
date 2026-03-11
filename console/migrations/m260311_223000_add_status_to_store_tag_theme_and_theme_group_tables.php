<?php

use yii\db\Migration;

class m260311_223000_add_status_to_store_tag_theme_and_theme_group_tables extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%store}}', 'status', $this->smallInteger()->notNull()->defaultValue(1));
        $this->addColumn('{{%tag}}', 'status', $this->smallInteger()->notNull()->defaultValue(1));
        $this->addColumn('{{%theme}}', 'status', $this->smallInteger()->notNull()->defaultValue(1));
        $this->addColumn('{{%theme_group}}', 'status', $this->smallInteger()->notNull()->defaultValue(1));

        $this->createIndex('{{%store_status_index}}', '{{%store}}', 'status');
        $this->createIndex('{{%tag_status_index}}', '{{%tag}}', 'status');
        $this->createIndex('{{%theme_status_index}}', '{{%theme}}', 'status');
        $this->createIndex('{{%theme_group_status_index}}', '{{%theme_group}}', 'status');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%store_status_index}}', '{{%store}}');
        $this->dropIndex('{{%tag_status_index}}', '{{%tag}}');
        $this->dropIndex('{{%theme_status_index}}', '{{%theme}}');
        $this->dropIndex('{{%theme_group_status_index}}', '{{%theme_group}}');

        $this->dropColumn('{{%store}}', 'status');
        $this->dropColumn('{{%tag}}', 'status');
        $this->dropColumn('{{%theme}}', 'status');
        $this->dropColumn('{{%theme_group}}', 'status');
    }
}
