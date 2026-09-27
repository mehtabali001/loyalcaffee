<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GalleryItem */
class GalleryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $caption = $this->t('caption');

        return [
            'id' => $this->id,
            'category' => $this->category,
            'caption' => $caption,
            'alt' => (app()->getLocale() === 'ar' ? $this->alt_ar : $this->alt_en) ?: $caption,
            'image' => media_url($this->image),
            'is_featured' => $this->is_featured,
        ];
    }
}
