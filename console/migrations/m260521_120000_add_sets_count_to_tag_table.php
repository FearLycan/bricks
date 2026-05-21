<?php

use yii\db\Migration;

/**
 * Adds the cached `sets_count` column to the `{{%tag}}` table.
 *
 * The value is recomputed by `php yii tag/recount-sets`.
 */
class m260521_120000_add_sets_count_to_tag_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%tag}}', 'sets_count', $this->integer()->notNull()->defaultValue(0)->after('status'));
        $this->createIndex('{{%tag_sets_count_index}}', '{{%tag}}', 'sets_count');
    }

    public function safeDown()
    {
        $this->dropIndex('{{%tag_sets_count_index}}', '{{%tag}}');
        $this->dropColumn('{{%tag}}', 'sets_count');
    }
}
