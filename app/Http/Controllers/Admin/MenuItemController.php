<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuItemController extends CrudController
{
    protected string $model = MenuItem::class;

    protected array $with = ['category:id,name'];

    protected array $searchable = ['name', 'description'];

    protected array $filters = ['menu_category_id', 'is_featured'];

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            ...$this->trans('name', true, 120),
            ...$this->trans('description', false, 500),
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'price_old' => ['nullable', 'numeric', 'min:0', 'max:99999', 'gt:price'],
            'price_note' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => [Rule::in(MenuItem::TAGS)],
            'is_featured' => ['boolean'],
            'is_available' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
