<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use App\Support\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MenuCategory extends Model
{
    use BustsContentCache, HasTranslations, Sortable;

    protected $fillable = ['slug', 'icon', 'image', 'is_active', 'sort_order'];

    protected array $translatable = ['name', 'description'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (self $c) {
            if (! $c->slug) {
                $base = Str::slug($c->name['en'] ?? '') ?: 'category';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $c->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $c->slug = $slug;
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
