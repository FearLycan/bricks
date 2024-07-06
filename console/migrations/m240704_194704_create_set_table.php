<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%set_image}}`.
 */
class m240704_194704_create_set_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%set}}', [
            'id'             => $this->primaryKey(),
            'number'         => $this->string(30),
            'name'           => $this->string(),
            'slug'           => $this->string(),
            'theme_id'       => $this->integer()->notNull(),
            'status'         => $this->smallInteger()->defaultValue(1),
            'number_variant' => $this->tinyInteger(),
            'minifigures'    => $this->tinyInteger(),
            'year'           => $this->smallInteger(),
            'pieces'         => $this->smallInteger(),
            'released'       => $this->boolean(),
            'brickset_url'   => $this->string(),
            'age'            => $this->smallInteger(),
            'created_at'     => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'     => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%set_theme_id_fk}}', '{{%set}}', 'theme_id', '{{%theme}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_number_index}}', '{{%set}}', 'number');
        $this->createIndex('{{%set_status_index}}', '{{%set}}', 'status');
        $this->createIndex('{{%set_name_index}}', '{{%set}}', 'name');
        $this->createIndex('{{%set_slug_index}}', '{{%set}}', 'slug');
        $this->createIndex('{{%set_year_index}}', '{{%set}}', 'year');
        $this->createIndex('{{%set_pieces_index}}', '{{%set}}', 'pieces');
        $this->createIndex('{{%set_released_index}}', '{{%set}}', 'released');
        $this->createIndex('{{%set_age_index}}', '{{%set}}', 'age');

        $this->createIndex('{{%set_created_at_index}}', '{{%set}}', 'created_at');
        $this->createIndex('{{%set_updated_at_index}}', '{{%set}}', 'updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%set}}');
    }
}
