<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

final class Locale
{
    public static function all(): array
    {
        return config('loyal.locales', ['en', 'ar']);
    }

    public static function default(): string
    {
        return self::all()[0];
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::all(), true);
    }

    public static function isRtl(?string $locale = null): bool
    {
        return in_array($locale ?? App::getLocale(), config('loyal.rtl_locales', []), true);
    }

    public static function pick(mixed $value, ?string $locale = null): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        $locale ??= App::getLocale();

        foreach ([$locale, self::default()] as $try) {
            if (isset($value[$try]) && $value[$try] !== '' && $value[$try] !== null) {
                return $value[$try];
            }
        }
        foreach ($value as $v) {
            if ($v !== '' && $v !== null) {
                return $v;
            }
        }

        return null;
    }

    /** Normalise incoming admin payloads so every locale key exists. */
    public static function normalise(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $value = [self::default() => $value];
        }
        $out = [];
        foreach (self::all() as $l) {
            $out[$l] = isset($value[$l]) ? trim((string) $value[$l]) : '';
        }

        return array_filter($out) ? $out : null;
    }
}
