<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use App\Support\Sortable;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use BustsContentCache, HasTranslations, Sortable;

    protected $fillable = ['author', 'avatar', 'location', 'role', 'rating', 'source', 'reviewed_on', 'is_active', 'sort_order'];

    protected array $translatable = ['content', 'location', 'role'];

    protected $casts = ['reviewed_on' => 'date:Y-m-d', 'is_active' => 'boolean', 'rating' => 'integer'];
}
