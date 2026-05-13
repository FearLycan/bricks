<?php

namespace common\helpers;

use Yii;

/**
 * Cache-backed sliding-window rate limiter.
 *
 * Usage:
 *   if (!RateLimiter::hit('login:' . $ip, 5, 900)) { // 5 attempts per 15 min
 *       throw new TooManyRequestsHttpException();
 *   }
 */
class RateLimiter
{
    /**
     * Increments the counter for the given key and returns true if the request
     * is within the allowed window, false if the limit has been exceeded.
     */
    public static function hit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $cacheKey = self::cacheKey($key);
        $cache = Yii::$app->cache;

        $count = (int)$cache->get($cacheKey);
        if ($count >= $maxAttempts) {
            return false;
        }

        $cache->set($cacheKey, $count + 1, $windowSeconds);
        return true;
    }

    /**
     * Resets the counter for a given key (e.g. after a successful login).
     */
    public static function reset(string $key): void
    {
        Yii::$app->cache->delete(self::cacheKey($key));
    }

    private static function cacheKey(string $key): string
    {
        return 'rl:' . $key;
    }
}
