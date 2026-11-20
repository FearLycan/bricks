<?php

namespace frontend\components;

use common\enums\StatusEnum;
use common\models\Set;

/**
 * LEGO advent calendars page (/lego/advent-calendars).
 *
 * Calendars are found by name ("... Advent Calendar <year>"), so a new
 * season's calendars appear on their own once Brickset has them. The short
 * "who is it for" copy is keyed by series (City, Friends, ...); a series
 * without copy gets a neutral line, so add copy here when LEGO starts a new one.
 */
final class AdventCalendars
{
    /**
     * Calendars are bought from late summer to Christmas, so from August on
     * the page is about this year's calendars, before that about last year's.
     */
    public static function seasonYear(?\DateTimeImmutable $today = null): int
    {
        $today ??= new \DateTimeImmutable('now');
        $year = (int)$today->format('Y');

        return (int)$today->format('n') >= 8 ? $year : $year - 1;
    }

    /**
     * Days left until the first door (1 December of the season year), or
     * null once December has started.
     */
    public static function daysToFirstDoor(?\DateTimeImmutable $today = null): ?int
    {
        $today = ($today ?? new \DateTimeImmutable('now'))->setTime(0, 0);
        $first = new \DateTimeImmutable(self::seasonYear($today) . '-12-01');
        if ($today >= $first) {
            return null;
        }

        return (int)$today->diff($first)->days;
    }

    /**
     * This season's calendars, plus last season's calendars still on sale
     * from series that have no calendar this year.
     *
     * @return array{current: list<array<string, mixed>>, older: list<array<string, mixed>>}
     */
    public static function load(int $year): array
    {
        $current = array_map([self::class, 'row'], self::find($year));
        usort($current, static fn(array $a, array $b) => [$a['priceCents'] ?? PHP_INT_MAX, $a['set']->number] <=> [$b['priceCents'] ?? PHP_INT_MAX, $b['set']->number]);

        $series = array_column($current, 'series');
        $today = date('Y-m-d');
        $older = [];
        foreach (self::find($year - 1) as $set) {
            $row = self::row($set);
            if (in_array($row['series'], $series, true)) {
                continue;
            }
            if ($set->exit_date !== null && $set->exit_date !== '' && $set->exit_date < $today) {
                continue;
            }
            $older[] = $row;
        }

        // Best price per piece among this year's calendars.
        $ppps = array_filter(array_column($current, 'ppp'));
        $best = $ppps !== [] ? min($ppps) : null;
        foreach ($current as &$row) {
            $row['best'] = $best !== null && $row['ppp'] === $best;
        }
        unset($row);

        return ['current' => $current, 'older' => $older];
    }

    /** @return Set[] */
    private static function find(int $year): array
    {
        return Set::find()
            ->with(['setPrices', 'mainImageRelation', 'theme'])
            ->where(['status' => StatusEnum::ACTIVE->value, 'year' => $year])
            ->andWhere(['like', 'name', 'Advent Calendar'])
            ->andWhere(['>', 'pieces', 0])
            ->all();
    }

    /** @return array<string, mixed> */
    private static function row(Set $set): array
    {
        $series = self::seriesOf($set);
        $price = $set->getLocalRetailPrice();
        $copy = self::copy()[$series] ?? null;

        return [
            'set'        => $set,
            'series'     => $series,
            'who'        => $copy[0] ?? T::tr('For a {series} fan', ['series' => $series]),
            'text'       => $copy[1] ?? T::tr('24 small builds from the {series} range, one behind each door.', ['series' => $series]),
            'price'      => $price,
            'priceCents' => $price['cents'] ?? null,
            'ppp'        => $price !== null && (int)$set->pieces > 0 ? round($price['cents'] / 100 / (int)$set->pieces, 3) : null,
        ];
    }

    /** "LEGO City Advent Calendar 2026" -> "City"; falls back to the theme name. */
    private static function seriesOf(Set $set): string
    {
        if (preg_match('/^(?:LEGO\s+)?(.+?)\s+(?:\d{4}\s+)?Advent Calendar/i', (string)$set->name, $m)) {
            return trim($m[1]);
        }

        return (string)($set->theme->name ?? $set->name);
    }

    /** @return array<string, array{0: string, 1: string}> */
    private static function copy(): array
    {
        return [
            'City'         => [T::tr('For a little firefighter or police officer'), T::tr('A small vehicle, building or figure from the city every day. The lowest age in the line-up, so a good first calendar.')],
            'Friends'      => [T::tr('For a Friends fan'), T::tr('Animals, decorations and small scenes from Heartlake City. Good for a child who likes to decorate and make up stories.')],
            'Star Wars'    => [T::tr('For a Star Wars fan'), T::tr('Micro starships and characters from the saga. Many adult fans buy it too, because the tiny models look good on a shelf.')],
            'Marvel'       => [T::tr('For a superhero fan'), T::tr('Mini vehicles and Marvel heroes, with the most pieces among the calendars at this price.')],
            'Disney'       => [T::tr('For a Disney fan'), T::tr('Characters and small scenes from Disney films, rated 5+. For a child who knows the heroes from the screen.')],
            'Harry Potter' => [T::tr('For a Harry Potter fan'), T::tr('Small scenes, objects and characters from the wizarding world, rated 7+.')],
            'Minecraft'    => [T::tr('For a Minecraft fan'), T::tr('Mini builds and characters from the game. Fans recognise every door straight away.')],
        ];
    }
}
