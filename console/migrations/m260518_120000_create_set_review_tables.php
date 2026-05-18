<?php

use yii\db\Migration;

class m260518_120000_create_set_review_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%set_review}}', [
            'id'            => $this->primaryKey(),
            'user_id'       => $this->integer()->notNull(),
            'set_id'        => $this->integer()->notNull(),
            'review_type'   => $this->string(20)->notNull()->defaultValue('simple'),
            'overall_score' => $this->decimal(4, 2)->notNull(),
            'title'         => $this->string(255)->null(),
            'content'       => $this->text()->null(),
            'status'        => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_at'    => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'    => $this->timestamp()->null(),
            'published_at'  => $this->timestamp()->null(),
        ], $tableOptions);

        $this->addForeignKey('{{%set_review_user_id_fk}}', '{{%set_review}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('{{%set_review_set_id_fk}}', '{{%set_review}}', 'set_id', '{{%set}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_review_user_set_uindex}}', '{{%set_review}}', ['user_id', 'set_id'], true);
        $this->createIndex('{{%set_review_set_status_index}}', '{{%set_review}}', ['set_id', 'status']);
        $this->createIndex('{{%set_review_user_index}}', '{{%set_review}}', 'user_id');
        $this->createIndex('{{%set_review_overall_score_index}}', '{{%set_review}}', 'overall_score');
        $this->createIndex('{{%set_review_review_type_index}}', '{{%set_review}}', 'review_type');
        $this->createIndex('{{%set_review_published_at_index}}', '{{%set_review}}', 'published_at');

        $this->createTable('{{%set_review_score}}', [
            'id'            => $this->primaryKey(),
            'set_review_id' => $this->integer()->notNull(),
            'dimension_key' => $this->string(50)->notNull(),
            'score'         => $this->decimal(4, 2)->notNull(),
        ], $tableOptions);

        $this->addForeignKey('{{%set_review_score_review_id_fk}}', '{{%set_review_score}}', 'set_review_id', '{{%set_review}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_review_score_review_dim_uindex}}', '{{%set_review_score}}', ['set_review_id', 'dimension_key'], true);
        $this->createIndex('{{%set_review_score_dimension_index}}', '{{%set_review_score}}', 'dimension_key');

        $this->createTable('{{%set_review_answer}}', [
            'id'            => $this->primaryKey(),
            'set_review_id' => $this->integer()->notNull(),
            'question_key'  => $this->string(60)->notNull(),
            'answer_value'  => $this->string(120)->null(),
            'answer_text'   => $this->text()->null(),
        ], $tableOptions);

        $this->addForeignKey('{{%set_review_answer_review_id_fk}}', '{{%set_review_answer}}', 'set_review_id', '{{%set_review}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%set_review_answer_review_index}}', '{{%set_review_answer}}', 'set_review_id');
        $this->createIndex('{{%set_review_answer_question_index}}', '{{%set_review_answer}}', 'question_key');
        $this->createIndex('{{%set_review_answer_question_value_index}}', '{{%set_review_answer}}', ['question_key', 'answer_value']);

        $this->createTable('{{%user_preference}}', [
            'id'               => $this->primaryKey(),
            'user_id'          => $this->integer()->notNull(),
            'preference_key'   => $this->string(60)->notNull(),
            'preference_value' => $this->string(120)->notNull(),
            'created_at'       => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'       => $this->timestamp()->null(),
        ], $tableOptions);

        $this->addForeignKey('{{%user_preference_user_id_fk}}', '{{%user_preference}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('{{%user_preference_user_key_value_uindex}}', '{{%user_preference}}', ['user_id', 'preference_key', 'preference_value'], true);
        $this->createIndex('{{%user_preference_key_value_index}}', '{{%user_preference}}', ['preference_key', 'preference_value']);
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('{{%user_preference_user_id_fk}}', '{{%user_preference}}');
        $this->dropTable('{{%user_preference}}');

        $this->dropForeignKey('{{%set_review_answer_review_id_fk}}', '{{%set_review_answer}}');
        $this->dropTable('{{%set_review_answer}}');

        $this->dropForeignKey('{{%set_review_score_review_id_fk}}', '{{%set_review_score}}');
        $this->dropTable('{{%set_review_score}}');

        $this->dropForeignKey('{{%set_review_set_id_fk}}', '{{%set_review}}');
        $this->dropForeignKey('{{%set_review_user_id_fk}}', '{{%set_review}}');
        $this->dropTable('{{%set_review}}');
    }
}