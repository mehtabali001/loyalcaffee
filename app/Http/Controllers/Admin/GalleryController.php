<?php

namespace App\Http\Controllers\Admin;

use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryController extends CrudController
{
    protected string $model = GalleryItem::class;

    protected array $searchable = ['caption', 'alt_en', 'alt_ar'];

    protected array $filters = ['category'];

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            'category' => ['required', Rule::in(GalleryItem::CATEGORIES)],
            ...$this->trans('caption', false, 160),
            'image' => ['required', 'string', 'max:255'],
            'alt_en' => ['nullable', 'string', 'max:160'],
            'alt_ar' => ['nullable', 'string', 'max:160'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /** POST {images: ["path", ...], category} — create many items after a multi-upload. */
    public function bulk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'images' => ['required', 'array', 'max:50'],
            'images.*' => ['string', 'max:255'],
            'category' => ['required', Rule::in(GalleryItem::CATEGORIES)],
        ]);
        $created = collect($data['images'])->map(fn ($p) => GalleryItem::create(['image' => $p, 'category' => $data['category']]));

        return response()->json(['data' => $created->map(fn ($m) => $this->present($m))], 201);
    }
}
