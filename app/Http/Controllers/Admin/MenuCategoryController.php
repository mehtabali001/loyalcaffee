<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuCategoryController extends CrudController
{
    protected string $model = MenuCategory::class;

    protected array $searchable = ['name', 'slug'];

    protected function query(Request $request): Builder
    {
        return parent::query($request)->withCount('items');
    }

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            ...$this->trans('name', true, 80),
            ...$this->trans('description', false, 400),
            'slug' => ['nullable', 'alpha_dash', 'max:80', Rule::unique('menu_categories', 'slug')->ignore($record?->id)],
            'icon' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
