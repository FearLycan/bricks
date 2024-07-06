<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%theme}}`.
 */
class m240704_053142_create_theme_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%theme_group}}', [
            'id'   => $this->primaryKey(),
            'name' => $this->string(),
            'slug' => $this->string(),

            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->createIndex('{{%theme_group_name_index}}', '{{%theme_group}}', 'name');
        $this->createIndex('{{%theme_group_slug_index}}', '{{%theme_group}}', 'slug');


        $this->createTable('{{%theme}}', [
            'id'         => $this->primaryKey(),
            'name'       => $this->string(),
            'slug'       => $this->string(),
            'parent_id'  => $this->integer()->null(),
            'group_id'   => $this->integer()->null(),
            'sets_count' => $this->smallInteger(),
            'year_from'  => $this->smallInteger(),
            'year_to'    => $this->smallInteger(),

            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%theme_parent_id_fk}}', '{{%theme}}', 'parent_id', '{{%theme}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('{{%theme_group_id_fk}}', '{{%theme}}', 'group_id', '{{%theme_group}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%theme_name_index}}', '{{%theme}}', 'name');
        $this->createIndex('{{%theme_slug_index}}', '{{%theme}}', 'slug');
        $this->createIndex('{{%theme_sets_count_index}}', '{{%theme}}', 'sets_count');
        $this->createIndex('{{%theme_year_from_index}}', '{{%theme}}', 'year_from');
        $this->createIndex('{{%theme_year_to_index}}', '{{%theme}}', 'year_to');

        $this->createIndex('{{%theme_created_at_index}}', '{{%theme}}', 'created_at');
        $this->createIndex('{{%theme_updated_at_index}}', '{{%theme}}', 'updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%theme}}');
    }
}
