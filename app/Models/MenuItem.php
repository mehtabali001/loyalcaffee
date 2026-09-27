<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use App\Support\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use BustsContentCache, HasTranslations, Sortable;

    public const TAGS = ['signature', 'new', 'popular', 'vegetarian', 'vegan', 'spicy', 'sharing'];

    protected $fillable = ['menu_category_id', 'price', 'price_old', 'price_note', 'image', 'tags',
        'is_featured', 'is_available', 'is_active', 'sort_order'];

    protected array $translatable = ['name', 'description'];

    protected $casts = [
        'tags' => 'array',
        'price' => 'decimal:2',
        'price_old' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }
}
