<?php

use App\Support\Locale;

if (! function_exists('tr')) {
    /**
     * Resolve a translatable JSON value ({"en": "...", "ar": "..."}) for the current locale,
     * falling back to the default locale, then to the first non-empty value.
     */
    function tr(mixed $value, ?string $locale = null): mixed
    {
        return Locale::pick($value, $locale);
    }
}

if (! function_exists('media_url')) {
    function media_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::disk(config('loyal.uploads.disk'))->url($path);
    }
}
