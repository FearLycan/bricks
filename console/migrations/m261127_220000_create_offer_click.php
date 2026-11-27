<?php

use yii\db\Migration;

/**
 * Clicks on store offers and on the "official LEGO price" button, sent by the page
 * script (navigator.sendBeacon), so a row means a browser that ran our JS. Admins are
 * not recorded; suspected bots are kept with is_bot = 1 and left out of the reports.
 * Ported from store-base (product_click).
 */
class m261127_220000_create_offer_click extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%offer_click}}', [
            'id'            => $this->bigPrimaryKey(),
            'kind'          => $this->string(10)->notNull()->defaultValue('offer'),
            'set_id'        => $this->integer()->notNull(),
            'set_offer_id'  => $this->integer()->null(),
            'store_id'      => $this->integer()->null(),
            'price'         => $this->integer()->null(),
            'currency_code' => $this->string(3)->null(),
            'lang'          => $this->string(5)->null(),
            'device'        => $this->string(10)->null(),
            'ua_hash'       => $this->string(64)->null(),
            'is_bot'        => $this->boolean()->notNull()->defaultValue(false),
            'created_at'    => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-offer_click-bot_created', '{{%offer_click}}', ['is_bot', 'created_at']);
        $this->createIndex('idx-offer_click-set', '{{%offer_click}}', ['set_id', 'created_at']);
        $this->createIndex('idx-offer_click-offer', '{{%offer_click}}', ['set_offer_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%offer_click}}');
    }
}
