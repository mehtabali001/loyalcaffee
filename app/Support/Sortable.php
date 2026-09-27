<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public static function bootSortable(): void
    {
        static::creating(function ($model) {
            if (! $model->sort_order) {
                $model->sort_order = (int) static::query()->max('sort_order') + 1;
            }
        });
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where($this->getTable().'.is_active', true);
    }
}
