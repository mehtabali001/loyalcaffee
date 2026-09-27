<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Event */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $full = $this->capacity !== null && (int) ($this->seats_taken ?? 0) >= $this->capacity;

        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'title' => $this->t('title'),
            'description' => $this->t('description'),
            'competition' => $this->competition,
            'stage' => $this->t('stage'),
            'home_team' => $this->t('home_team'),
            'away_team' => $this->t('away_team'),
            'home_flag' => $this->home_flag,
            'away_flag' => $this->away_flag,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'image' => media_url($this->image),
            'offer' => $this->t('offer'),
            'is_big_match' => $this->is_big_match,
            'reservations_open' => $this->reservations_open && ! $full,
            'is_full' => $full,
        ];
    }
}
