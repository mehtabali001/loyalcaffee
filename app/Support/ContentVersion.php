<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache-busting without cache tags (works on file/database/redis stores alike).
 * Every public cache key embeds the current version; any content write bumps it.
 */
final class ContentVersion
{
    private const KEY = 'loyal:content-version';

    public static function current(): int
    {
        return (int) Cache::rememberForever(self::KEY, fn () => time());
    }

    public static function bump(): void
    {
        Cache::forever(self::KEY, self::current() + 1);
    }

    public static function remember(string $key, \Closure $callback, ?int $ttl = null): mixed
    {
        $full = sprintf('loyal:v%d:%s:%s', self::current(), app()->getLocale(), $key);

        return Cache::remember($full, $ttl ?? config('loyal.cache_ttl'), $callback);
    }
}
