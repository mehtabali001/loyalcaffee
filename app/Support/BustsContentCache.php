<?php

namespace App\Support;

trait BustsContentCache
{
    public static function bootBustsContentCache(): void
    {
        static::saved(fn () => ContentVersion::bump());
        static::deleted(fn () => ContentVersion::bump());
    }
}
