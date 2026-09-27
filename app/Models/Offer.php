<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use App\Support\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use BustsContentCache, HasTranslations, Sortable;

    protected $fillable = ['image', 'cta_link', 'starts_on', 'ends_on', 'is_active', 'sort_order'];

    protected array $translatable = ['title', 'description', 'badge'];

    protected $casts = ['starts_on' => 'date:Y-m-d', 'ends_on' => 'date:Y-m-d', 'is_active' => 'boolean'];

    public function scopeCurrent(Builder $q): Builder
    {
        $today = now()->toDateString();

        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_on')->orWhere('starts_on', '<=', $today))
            ->where(fn ($w) => $w->whereNull('ends_on')->orWhere('ends_on', '>=', $today));
    }
}
