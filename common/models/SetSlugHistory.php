<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * A slug a set used to have. LegoController::actionView answers it with a 301 to the
 * set's current URL. Written by Set::rebuildSlug().
 *
 * @property int    $id
 * @property int    $set_id
 * @property string $slug
 * @property int    $created_at
 */
class SetSlugHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%set_slug_history}}';
    }

    /**
     * Remembers $old for the set and forgets $new (a slug that is current again must not redirect).
     */
    public static function record(int $setId, ?string $old, ?string $new): void
    {
        $db = static::getDb();
        if ($new !== null && $new !== '') {
            $db->createCommand()->delete(static::tableName(), ['slug' => $new])->execute();
        }
        if ($old === null || $old === '' || $old === $new) {
            return;
        }
        // Upsert: the same old slug may have belonged to another set before.
        $db->createCommand()->upsert(static::tableName(), [
            'set_id'     => $setId,
            'slug'       => $old,
            'created_at' => time(),
        ], ['set_id' => $setId, 'created_at' => time()])->execute();
    }

    public static function findSet(string $slug): ?Set
    {
        $setId = static::find()->select('set_id')->where(['slug' => $slug])->scalar();

        return $setId ? Set::findOne((int)$setId) : null;
    }
}
