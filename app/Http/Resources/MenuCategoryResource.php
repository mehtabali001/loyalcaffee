<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MenuCategory */
class MenuCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->t('name'),
            'description' => $this->t('description'),
            'icon' => $this->icon,
            'image' => media_url($this->image),
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
