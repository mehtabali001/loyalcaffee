<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use App\Support\Sortable;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use BustsContentCache, HasTranslations, Sortable;

    public const CATEGORIES = ['interior', 'shisha', 'drinks', 'food', 'match-nights'];

    protected $fillable = ['category', 'image', 'alt_en', 'alt_ar', 'is_featured', 'is_active', 'sort_order'];

    protected array $translatable = ['caption'];

    protected $casts = ['is_featured' => 'boolean', 'is_active' => 'boolean'];
}
