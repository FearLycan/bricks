<?php

namespace common\components;

/**
 * Single source of truth for seasonal event windows used across the app
 * (homepage spotlight, navbar dropdown bonus item, etc.).
 *
 * Returns a normalized event payload when the current date falls into one
 * of the configured windows, or null otherwise.
 */
final class SeasonalEventResolver
{
    public const KEY_HALLOWEEN = 'halloween';
    public const KEY_CHRISTMAS = 'christmas';
    public const KEY_EASTER    = 'easter';
    public const KEY_VALENTINE = 'valentine';

    /**
     * Resolve the active event for the given date (defaults to today).
     *
     * Order matters: when windows overlap, the first matching entry wins.
     * Christmas takes precedence over Halloween at the boundary because it
     * runs into January and is a stronger commercial signal.
     *
     * @return array{key:string,name_filter:string,icon:string,image:string}|null
     */
    public static function getActiveEvent(?\DateTimeImmutable $today = null): ?array
    {
        $today = $today ?? new \DateTimeImmutable('today');

        if (self::isInChristmasWindow($today)) {
            return self::eventPayload(self::KEY_CHRISTMAS, 'Christmas', 'bi-tree', 'images/seasonal/christmas.jpg');
        }

        if (self::isInHalloweenWindow($today)) {
            return self::eventPayload(self::KEY_HALLOWEEN, 'Halloween', 'bi-emoji-dizzy', 'images/seasonal/halloween.jpg');
        }

        if (self::isInEasterWindow($today)) {
            return self::eventPayload(self::KEY_EASTER, 'Easter', 'bi-egg', 'images/seasonal/easter.jpg');
        }

        if (self::isInValentineWindow($today)) {
            return self::eventPayload(self::KEY_VALENTINE, 'Valentine', 'bi-heart-fill', 'images/seasonal/valentines.jpg');
        }

        return null;
    }

    /**
     * Christmas window: Nov 15 .. Jan 1 (spans year boundary).
     */
    private static function isInChristmasWindow(\DateTimeImmutable $today): bool
    {
        $md = (int)$today->format('md');

        return $md >= 1115 || $md <= 101;
    }

    /**
     * Halloween window: Sep 25 .. Nov 7.
     */
    private static function isInHalloweenWindow(\DateTimeImmutable $today): bool
    {
        $md = (int)$today->format('md');

        return $md >= 925 && $md <= 1107;
    }

    /**
     * Easter window: 28 days before Easter Sunday .. 7 days after.
     * Easter date is computed via PHP's gregorian-easter algorithm.
     */
    private static function isInEasterWindow(\DateTimeImmutable $today): bool
    {
        $year = (int)$today->format('Y');
        $easter = self::easterDate($year);
        if ($easter === null) {
            return false;
        }

        $start = $easter->modify('-28 days');
        $end   = $easter->modify('+7 days');

        return $today >= $start && $today <= $end;
    }

    /**
     * Valentine's window: Jan 25 .. Feb 15.
     */
    private static function isInValentineWindow(\DateTimeImmutable $today): bool
    {
        $md = (int)$today->format('md');

        return $md >= 125 && $md <= 215;
    }

    private static function easterDate(int $year): ?\DateTimeImmutable
    {
        if (!function_exists('easter_date')) {
            return null;
        }

        $timestamp = @easter_date($year);
        if ($timestamp === false) {
            return null;
        }

        return (new \DateTimeImmutable('@' . $timestamp))->setTime(0, 0);
    }

    /**
     * @return array{key:string,name_filter:string,icon:string,image:string}
     */
    private static function eventPayload(string $key, string $nameFilter, string $icon, string $image): array
    {
        return [
            'key'         => $key,
            'name_filter' => $nameFilter,
            'icon'        => $icon,
            'image'       => $image,
        ];
    }
}
