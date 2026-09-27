<?php

namespace App\Http\Controllers\Admin;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OfferController extends CrudController
{
    protected string $model = Offer::class;

    protected array $searchable = ['title'];

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            ...$this->trans('title', true, 120),
            ...$this->trans('description', false, 400),
            ...$this->trans('badge', false, 40),
            'image' => ['nullable', 'string', 'max:255'],
            'cta_link' => ['nullable', 'string', 'max:255'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['boolean'],
        ];
    }
}
