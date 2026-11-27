<?php

use yii\db\Migration;

/**
 * Cookieless traffic stats (ported from store-base): daily counters only, no IPs,
 * no per-visitor rows. traffic_daily also keeps the page language.
 */
class m261127_200000_create_traffic_stats extends Migration
{
    public function safeUp()
    {
        // Page views by page type, language, traffic source and device. entries = views that
        // arrived from outside the site (a referrer that is not us, or none): visit starts.
        $this->createTable('{{%traffic_daily}}', [
            'day'       => $this->date()->notNull(),
            'page_type' => $this->string(16)->notNull(),
            'lang'      => $this->string(5)->notNull(),
            'source'    => $this->string(16)->notNull(),
            'device'    => $this->string(8)->notNull(),
            'views'     => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'entries'   => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[day]], [[page_type]], [[lang]], [[source]], [[device]])',
        ]);

        // Per URL path (query string dropped): landing pages and 404s.
        $this->createTable('{{%traffic_path_daily}}', [
            'day'       => $this->date()->notNull(),
            'path_hash' => $this->char(32)->notNull(),
            'path'      => $this->string(255)->notNull(),
            'page_type' => $this->string(16)->notNull(),
            'views'     => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'entries'   => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[day]], [[path_hash]])',
        ]);
        $this->createIndex('idx-traffic_path_daily-type', '{{%traffic_path_daily}}', ['page_type', 'day']);

        // External referrer hosts (entries only).
        $this->createTable('{{%traffic_referrer_daily}}', [
            'day'     => $this->date()->notNull(),
            'host'    => $this->string(100)->notNull(),
            'source'  => $this->string(16)->notNull(),
            'entries' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[day]], [[host]])',
        ]);

        // Crawler / automated hits by bot family and page type.
        $this->createTable('{{%traffic_bot_daily}}', [
            'day'       => $this->date()->notNull(),
            'bot'       => $this->string(16)->notNull(),
            'page_type' => $this->string(16)->notNull(),
            'hits'      => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[day]], [[bot]], [[page_type]])',
        ]);

        // On-site searches (catalog name search): what people look for, what finds nothing.
        $this->createTable('{{%search_daily}}', [
            'day'        => $this->date()->notNull(),
            'query_hash' => $this->char(32)->notNull(),
            'query'      => $this->string(100)->notNull(),
            'searches'   => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'results'    => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[day]], [[query_hash]])',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%search_daily}}');
        $this->dropTable('{{%traffic_bot_daily}}');
        $this->dropTable('{{%traffic_referrer_daily}}');
        $this->dropTable('{{%traffic_path_daily}}');
        $this->dropTable('{{%traffic_daily}}');
    }
}
