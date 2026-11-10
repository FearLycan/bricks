<?php

use yii\db\Migration;

/**
 * Deactivates the AliExpress store.
 *
 * AliExpress listings matched to LEGO sets are overwhelmingly non-genuine
 * (compatible / knock-off bricks). Shown next to the official LEGO price they
 * produced fake "-75%" deals on /lego/on-sale and set pages.
 *
 * Offers are NOT deleted: with the store inactive they are hidden from every
 * public query (Set::getSetOffers(), promo listings, feeds, schema) and the
 * AliExpress discovery/review crons stop. Reactivate the store in the admin
 * panel to bring them back.
 */
class m261111_120000_deactivate_aliexpress_store extends Migration
{
    public function safeUp(): void
    {
        $this->update('{{%store}}', ['status' => 0], ['code' => 'ALIEXPRESS']);
    }

    public function safeDown(): void
    {
        $this->update('{{%store}}', ['status' => 1], ['code' => 'ALIEXPRESS']);
    }
}
