<?php

namespace console\controllers;

use common\enums\StatusEnum;
use common\models\Set;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Inflector;

final class SetController extends Controller
{
    public function actionRebuildSlugs(?string $number = null, bool $onlyEmpty = false): int
    {
        $query = Set::find();

        if ($number !== null) {
            $query->andWhere(['number' => $number]);
        }

        if ($onlyEmpty) {
            $query->andWhere(['or', ['slug' => null], ['slug' => '']]);
        }

        $updated = 0;
        $skipped = 0;

        /** @var Set $set */
        foreach ($query->each() as $set) {
            $oldSlug = $set->slug;
            $set->rebuildSlug();
            $set->refresh();

            if ($set->slug === $oldSlug) {
                $skipped++;
                continue;
            }

            echo "{$set->number}: {$oldSlug} -> {$set->slug}\n";
            $updated++;
        }

        echo "\nDone. Updated: {$updated}, unchanged: {$skipped}\n";

        return ExitCode::OK;
    }

    public function actionRebuildSlugsMatchingNumber(): int
    {
        $query = Set::find()
            ->andWhere(['not', ['slug' => null]])
            ->andWhere(['not', ['number' => null]])
            ->andWhere(['status' => StatusEnum::ACTIVE->value])
            ->andWhere('slug = number');

        $updated = 0;
        $skipped = 0;

        /** @var Set $set */
        foreach ($query->each() as $set) {
            $oldSlug = $set->slug;
            $set->rebuildSlug();
            $set->refresh();

            if ($set->slug === $oldSlug) {
                $skipped++;
                continue;
            }

            echo "{$set->number}: {$oldSlug} -> {$set->slug}\n";
            $updated++;
        }

        echo "\nDone. Updated: {$updated}, unchanged: {$skipped}\n";

        return ExitCode::OK;
    }

    public function actionListBrokenSlugs(): int
    {
        $broken = 0;

        /** @var Set $set */
        foreach (Set::find()->each() as $set) {
            $expected = Inflector::slug(trim(($set->name ?? '') . ' ' . ($set->number ?? '')));
            if ($expected === '') {
                continue;
            }

            if ((string)$set->slug !== $expected) {
                echo "{$set->number}\t{$set->slug}\t-> expected: {$expected}\n";
                $broken++;
            }
        }

        echo "\nBroken slugs: {$broken}\n";

        return ExitCode::OK;
    }
}
