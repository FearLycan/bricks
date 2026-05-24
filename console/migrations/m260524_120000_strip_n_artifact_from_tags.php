<?php

use yii\db\Migration;

/**
 * Strips the `|n` artifact left by an older Brickset extendedData.tags importer
 * pass. Affected rows in the `{{%tag}}` table had names like `Rhino|n` with
 * slugs like `rhinon` — both are cleaned to `Rhino` / `rhino`.
 *
 * The cleanup happens in four steps to handle 7 known cases where the cleaned
 * slug would collide with an already-existing canonical tag (e.g. `Bob-omb|n`
 * collides with `Bob-omb`):
 *
 *   1. Copy `set_tag` rows from the broken tag to the existing clean twin
 *      (INSERT IGNORE handles the unique (set_id, tag_id) constraint).
 *   2. Delete leftover `set_tag` rows that still point at the colliders.
 *   3. Delete the 7 colliding broken tag rows themselves.
 *   4. UPDATE the remaining ~2417 broken tags in place — strip `|n` from name,
 *      trim the trailing `n` from slug. No collisions possible at this point.
 *
 * Routing already handles incoming legacy `slug+n` URLs via a 301 fallback in
 * {@see frontend\modules\lego\controllers\LegoController::actionTag()}, so this
 * migration does not need to track redirects.
 *
 * Forward-only — there is no sensible inverse for "I lost the `|n` suffix".
 */
class m260524_120000_strip_n_artifact_from_tags extends Migration
{
    public function safeUp(): void
    {
        $db = $this->db;

        // Step 1: copy set_tag rows from each colliding broken tag to its clean twin.
        $db->createCommand(<<<SQL
            INSERT IGNORE INTO {{%set_tag}} (set_id, tag_id, created_at, updated_at)
            SELECT st.set_id, t_clean.id, st.created_at, st.updated_at
            FROM {{%set_tag}} st
            JOIN {{%tag}} t_broken ON t_broken.id = st.tag_id AND t_broken.name LIKE '%|n'
            JOIN {{%tag}} t_clean
              ON t_clean.slug = SUBSTRING(t_broken.slug, 1, LENGTH(t_broken.slug) - 1)
             AND t_clean.id <> t_broken.id
        SQL)->execute();

        // Step 2: delete the now-redundant set_tag rows still pointing at colliders.
        $db->createCommand(<<<SQL
            DELETE st FROM {{%set_tag}} st
            JOIN {{%tag}} t_broken ON t_broken.id = st.tag_id AND t_broken.name LIKE '%|n'
            JOIN {{%tag}} t_clean
              ON t_clean.slug = SUBSTRING(t_broken.slug, 1, LENGTH(t_broken.slug) - 1)
             AND t_clean.id <> t_broken.id
        SQL)->execute();

        // Step 3: delete the colliding broken tag rows themselves.
        $db->createCommand(<<<SQL
            DELETE t_broken FROM {{%tag}} t_broken
            JOIN {{%tag}} t_clean
              ON t_clean.slug = SUBSTRING(t_broken.slug, 1, LENGTH(t_broken.slug) - 1)
             AND t_clean.id <> t_broken.id
            WHERE t_broken.name LIKE '%|n'
        SQL)->execute();

        // Step 4: in-place rename for the remaining broken tags (no twin existed).
        $db->createCommand(<<<SQL
            UPDATE {{%tag}}
            SET name = TRIM(TRAILING '|n' FROM name),
                slug = SUBSTRING(slug, 1, LENGTH(slug) - 1)
            WHERE name LIKE '%|n'
        SQL)->execute();

        // Optional: merge slug duplicates that pre-date this importer fix. The
        // canonical row is the lower-id (older) one; refs from the higher-id
        // duplicate are moved to it. INSERT IGNORE handles the (set_id, tag_id)
        // unique constraint.
        $duplicates = $db->createCommand(<<<SQL
            SELECT slug FROM {{%tag}}
            WHERE slug IS NOT NULL AND slug <> ''
            GROUP BY slug HAVING COUNT(*) > 1
        SQL)->queryColumn();

        foreach ($duplicates as $slug) {
            $ids = $db->createCommand(
                'SELECT id FROM {{%tag}} WHERE slug = :slug ORDER BY id',
                [':slug' => $slug]
            )->queryColumn();

            $keepId = (int)array_shift($ids);
            foreach ($ids as $mergeId) {
                $mergeId = (int)$mergeId;
                $db->createCommand(
                    'INSERT IGNORE INTO {{%set_tag}} (set_id, tag_id, created_at, updated_at)'
                    . ' SELECT set_id, :keep, created_at, updated_at'
                    . ' FROM {{%set_tag}} WHERE tag_id = :merge',
                    [':keep' => $keepId, ':merge' => $mergeId]
                )->execute();
                $db->createCommand('DELETE FROM {{%set_tag}} WHERE tag_id = :merge', [':merge' => $mergeId])->execute();
                $db->createCommand('DELETE FROM {{%tag}} WHERE id = :merge', [':merge' => $mergeId])->execute();
            }
        }
    }

    public function safeDown(): bool
    {
        // No-op: the original `|n` suffixes and merged duplicate rows cannot be
        // reconstructed from the cleaned data.
        echo "m260524_120000_strip_n_artifact_from_tags cannot be reverted.\n";

        return false;
    }
}
