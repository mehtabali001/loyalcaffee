<?php

namespace App\Models;

use App\Support\BustsContentCache;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use BustsContentCache;

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => \App\Support\UnicodeJson::class];

    /** Nested messages for one locale: ['nav' => ['menu' => 'Menu']] */
    public static function messagesFor(string $locale): array
    {
        $out = [];
        foreach (static::query()->get(['key', 'value']) as $row) {
            $v = $row->value[$locale] ?? null;
            if ($v !== null && $v !== '') {
                data_set($out, $row->key, $v);
            }
        }

        return $out;
    }
}
