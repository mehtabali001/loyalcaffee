<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MenuItem */
class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->menu_category_id,
            'name' => $this->t('name'),
            'description' => $this->t('description'),
            'price' => $this->price !== null ? (float) $this->price : null,
            'price_old' => $this->price_old !== null ? (float) $this->price_old : null,
            'price_note' => $this->price_note,
            'image' => media_url($this->image),
            'tags' => $this->tags ?? [],
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
        ];
    }
}
