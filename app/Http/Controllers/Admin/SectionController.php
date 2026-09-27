<?php

namespace App\Http\Controllers\Admin;

use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends CrudController
{
    protected string $model = Section::class;

    protected array $media = ['image', 'image_secondary', 'video'];

    protected array $filters = ['page', 'type'];

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            'page' => ['required', 'string', 'max:40'],
            'type' => ['required', Rule::in(Section::TYPES)],
            ...$this->trans('title', false, 200),
            ...$this->trans('subtitle', false, 300),
            ...$this->trans('body', false, 3000),
            ...$this->trans('cta_label', false, 60),
            'cta_link' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_secondary' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }
}
