<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestimonialController extends CrudController
{
    protected string $model = Testimonial::class;

    protected array $searchable = ['author', 'content'];

    protected array $media = [];

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            'author' => ['required', 'string', 'max:80'],
            ...$this->trans('content', true, 800),
            'rating' => ['required', 'integer', 'between:1,5'],
            'source' => ['required', 'in:google,instagram,tripadvisor,in-store'],
            'reviewed_on' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
