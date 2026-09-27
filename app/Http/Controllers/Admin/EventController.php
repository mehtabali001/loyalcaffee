<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EventController extends CrudController
{
    protected string $model = Event::class;

    protected array $searchable = ['title', 'home_team', 'away_team', 'competition'];

    protected array $filters = ['kind'];

    protected string $defaultSort = 'starts_at';

    protected ?int $perPage = 30;

    protected function query(Request $request): Builder
    {
        $q = parent::query($request)->withSum(['reservations as seats_taken' => fn ($r) => $r->whereIn('status', ['pending', 'confirmed', 'seated'])], 'guests');

        return match ($request->query('when')) {
            'upcoming' => $q->reorder()->where('starts_at', '>=', now()->subHours(3))->orderBy('starts_at'),
            'past' => $q->where('starts_at', '<', now()->subHours(3)),
            default => $q,
        };
    }

    protected function rules(Request $request, ?Model $record): array
    {
        return [
            'kind' => ['required', 'in:match,event'],
            ...$this->trans('title', true, 160),
            ...$this->trans('description', false, 1000),
            'competition' => ['nullable', 'string', 'max:80'],
            ...$this->trans('stage', false, 60),
            ...$this->trans('home_team', false, 60),
            ...$this->trans('away_team', false, 60),
            'home_flag' => ['nullable', 'alpha', 'max:8'],
            'away_flag' => ['nullable', 'alpha', 'max:8'],
            'starts_at' => ['required', 'date'],
            'image' => ['nullable', 'string', 'max:255'],
            ...$this->trans('offer', false, 200),
            'is_big_match' => ['boolean'],
            'reservations_open' => ['boolean'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'is_active' => ['boolean'],
        ];
    }
}
