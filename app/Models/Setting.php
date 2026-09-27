<?php

namespace App\Models;

use App\Support\BustsContentCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use BustsContentCache;

    protected $fillable = ['group', 'key', 'value'];

    protected $casts = ['value' => \App\Support\UnicodeJson::class];

    private static ?array $memo = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::flushMemo());
        static::deleted(fn () => self::flushMemo());
    }

    /** All settings as key => value, cached. */
    public static function allAsArray(): array
    {
        return self::$memo ??= Cache::rememberForever('loyal:settings', fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return data_get(self::allAsArray(), $key, $default);
    }

    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public static function flushMemo(): void
    {
        self::$memo = null;
        Cache::forget('loyal:settings');
    }
}
