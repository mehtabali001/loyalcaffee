<?php

namespace App\Models;

use App\Support\BustsContentCache;
use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use BustsContentCache, HasTranslations;

    protected $fillable = ['kind', 'competition', 'home_flag', 'away_flag', 'starts_at', 'image',
        'is_big_match', 'reservations_open', 'capacity', 'is_active'];

    protected array $translatable = ['title', 'description', 'stage', 'home_team', 'away_team', 'offer'];

    protected $casts = [
        'starts_at' => 'datetime',
        'is_big_match' => 'boolean',
        'reservations_open' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Matches still worth showing: kick-off within the last 2 hours or in the future. */
    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('starts_at', '>=', now()->subHours(2))->orderBy('starts_at');
    }

    public function seatsTaken(): int
    {
        return (int) $this->reservations()->whereIn('status', ['pending', 'confirmed', 'seated'])->sum('guests');
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->seatsTaken() >= $this->capacity;
    }
}
