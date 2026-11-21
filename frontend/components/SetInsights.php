<?php

namespace frontend\components;

use common\enums\StatusEnum;
use common\models\Set;
use Yii;

/**
 * "Compared with the rest of the theme" box on the set page.
 *
 * Everything is derived from our own catalogue (LEGO list price in USD,
 * pieces, minifigures, Brickset launch/exit dates), so every set page gets
 * a few sentences and numbers no other page has. A card or sentence is
 * only produced when there is enough data behind it.
 */
final class SetInsights
{
    /** Fewer sets than this in a theme: no comparison. */
    public const MIN_THEME_SETS = 5;

    /** Price comparison uses sets from year ±YEAR_WINDOW. */
    public const YEAR_WINDOW = 3;

    private const CACHE_SECONDS = 43200;

    /**
     * Comparison base: sets sold on their own with at least this many pieces.
     * Keychains, magnets, polybag promos and gifts with purchase would
     * otherwise drag the theme minimum to 1 piece and the median down.
     */
    public const MIN_COMPARABLE_PIECES = 30;

    private const NOT_SOLD_ALONE = ['Magazine gift', 'Promotional', 'LEGO Gift with Purchase', 'Insiders Reward'];

    /**
     * @return array{
     *   theme: string,
     *   size: array{pieces: int, min: int, max: int, median: int, position: int, percentile: int}|null,
     *   ppp: array{cents: int, medianCents: int, position: int, cheaperThan: int, pricierThan: int, fromYear: int, toYear: int}|null,
     *   dates: array{launch: string|null, exit: string|null, upcoming: bool, ended: bool, months: int|null, typicalMonths: int|null},
     *   minifigs: array{count: int, typical: int}|null
     * }|null
     */
    public static function forSet(Set $set): ?array
    {
        $theme = $set->theme;
        if ($theme === null || (int)$set->pieces < 1) {
            return null;
        }

        $rows = self::themeRows((int)$set->theme_id);
        if (count($rows) < self::MIN_THEME_SETS) {
            return null;
        }

        $result = [
            'theme'    => (string)$theme->name,
            'size'     => self::size((int)$set->pieces, $rows),
            'ppp'      => self::pricePerPiece($set, $rows),
            'dates'    => self::dates($set, $rows),
            'minifigs' => self::minifigs($set, $rows),
        ];

        if ($result['size'] === null && $result['ppp'] === null) {
            return null;
        }

        return $result;
    }

    /**
     * Comparable sets of a theme: [pieces, price, year, launch, exit, minifigures].
     *
     * @return list<array{0:int,1:int,2:int,3:?string,4:?string,5:int}>
     */
    private static function themeRows(int $themeId): array
    {
        return Yii::$app->cache->getOrSet(['set-insights-theme', $themeId, 2], static function () use ($themeId): array {
            $rows = Set::find()
                ->select(['pieces', 'price', 'year', 'launch_date', 'exit_date', 'minifigures'])
                ->where(['theme_id' => $themeId, 'status' => StatusEnum::ACTIVE->value])
                ->andWhere(['>=', 'pieces', self::MIN_COMPARABLE_PIECES])
                ->andWhere(['or', ['availability' => null], ['not', ['availability' => self::NOT_SOLD_ALONE]]])
                ->asArray()
                ->all();

            return array_map(static fn(array $r): array => [
                (int)$r['pieces'],
                (int)$r['price'],
                (int)$r['year'],
                $r['launch_date'] ?: null,
                $r['exit_date'] ?: null,
                (int)$r['minifigures'],
            ], $rows);
        }, self::CACHE_SECONDS);
    }

    private static function size(int $pieces, array $rows): ?array
    {
        $all = array_column($rows, 0);
        sort($all);
        $n = count($all);
        $fewer = count(array_filter($all, static fn(int $p): bool => $p < $pieces));

        return [
            'pieces'     => $pieces,
            'min'        => $all[0],
            'max'        => $all[$n - 1],
            'median'     => self::median($all),
            'percentile' => (int)round($fewer * 100 / $n),
            'position'   => self::position($fewer, $n),
        ];
    }

    private static function pricePerPiece(Set $set, array $rows): ?array
    {
        $price = (int)$set->price;
        $year = (int)$set->year;
        if ($price <= 0 || $year <= 0) {
            return null;
        }

        $ppp = [];
        foreach ($rows as [$p, $pr, $y]) {
            if ($pr > 0 && $y > 0 && abs($y - $year) <= self::YEAR_WINDOW) {
                $ppp[] = $pr / $p;
            }
        }
        if (count($ppp) < self::MIN_THEME_SETS) {
            return null;
        }

        $mine = $price / (int)$set->pieces;
        sort($ppp);
        $n = count($ppp);
        $cheaper = count(array_filter($ppp, static fn(float $v): bool => $v < $mine));
        $pricier = count(array_filter($ppp, static fn(float $v): bool => $v > $mine));

        return [
            'cents'       => max(1, (int)round($mine)),
            'medianCents' => max(1, (int)round(self::median($ppp))),
            'position'    => self::position($cheaper, $n),
            // Share of comparable sets that cost more per piece than this one.
            'cheaperThan' => (int)round($pricier * 100 / $n),
            // Share of comparable sets that cost less per piece than this one.
            'pricierThan' => (int)round($cheaper * 100 / $n),
            'fromYear'    => $year - self::YEAR_WINDOW,
            'toYear'      => min((int)date('Y'), $year + self::YEAR_WINDOW),
        ];
    }

    private static function dates(Set $set, array $rows): array
    {
        $launch = $set->launch_date ?: null;
        $exit = $set->exit_date ?: null;
        $today = date('Y-m-d');

        $durations = [];
        foreach ($rows as [, , , $l, $e]) {
            if ($l !== null && $e !== null && $e > $l && $e <= $today) {
                $durations[] = self::monthsBetween($l, $e);
            }
        }

        return [
            'launch'        => $launch,
            'exit'          => $exit,
            'upcoming'      => $launch !== null && $launch > $today,
            'ended'         => $exit !== null && $exit <= $today,
            'months'        => $launch !== null && $exit !== null && $exit > $launch ? self::monthsBetween($launch, $exit) : null,
            'typicalMonths' => count($durations) >= self::MIN_THEME_SETS ? (int)round(self::median($durations)) : null,
        ];
    }

    private static function minifigs(Set $set, array $rows): ?array
    {
        $typical = (int)round(self::median(array_column($rows, 5)));
        $count = (int)$set->minifigures;
        if ($typical < 1 && $count < 1) {
            return null;
        }

        return ['count' => $count, 'typical' => $typical];
    }

    /** "August 2026" in the page language (standalone month name; "2026年8月" in Japanese). */
    public static function formatMonth(string $date): string
    {
        $pattern = str_starts_with((string)Yii::$app->language, 'ja') ? 'y年M月' : 'LLLL yyyy';

        return (string)Yii::$app->formatter->asDate($date, $pattern);
    }

    /** Sentence separator: none between Japanese sentences, a space elsewhere. */
    public static function joinSentences(array $sentences): string
    {
        return implode(str_starts_with((string)Yii::$app->language, 'ja') ? '' : ' ', array_filter($sentences, static fn($x) => $x !== ''));
    }

    /** Marker position on a 0–100 bar (kept off the very edges). */
    private static function position(int $below, int $n): int
    {
        return max(3, min(97, (int)round(($below + 0.5) * 100 / $n)));
    }

    private static function median(array $values): float
    {
        sort($values);
        $n = count($values);
        if ($n === 0) {
            return 0.0;
        }
        $mid = intdiv($n, 2);

        return $n % 2 ? (float)$values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }

    private static function monthsBetween(string $from, string $to): int
    {
        $d = (new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to));

        return max(1, $d->y * 12 + $d->m + ($d->d >= 15 ? 1 : 0));
    }
}
