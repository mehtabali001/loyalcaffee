<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Offer */
class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->t('title'),
            'description' => $this->t('description'),
            'badge' => $this->t('badge'),
            'image' => media_url($this->image),
            'cta_link' => $this->cta_link,
            'ends_on' => $this->ends_on?->toDateString(),
        ];
    }
}
