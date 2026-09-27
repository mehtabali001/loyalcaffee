<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Testimonial */
class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author' => $this->author,
            'avatar' => media_url($this->avatar),
            'location' => $this->t('location'),
            'role' => $this->t('role'),
            'content' => $this->t('content'),
            'rating' => (int) $this->rating,
            'source' => $this->source,
            'reviewed_on' => $this->reviewed_on?->format('Y-m-d'),
        ];
    }
}
