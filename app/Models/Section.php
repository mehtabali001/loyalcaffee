<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use App\Support\Sortable;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use BustsContentCache, HasTranslations, Sortable;

    public const TYPES = ['hero', 'story', 'day_night', 'featured_menu', 'matches', 'offers', 'gallery', 'testimonials', 'visit'];

    protected $fillable = ['page', 'type', 'cta_link', 'image', 'image_secondary', 'video', 'data', 'is_active', 'sort_order'];

    protected array $translatable = ['title', 'subtitle', 'body', 'cta_label'];

    protected $casts = ['data' => \App\Support\UnicodeJson::class, 'is_active' => 'boolean'];
}
