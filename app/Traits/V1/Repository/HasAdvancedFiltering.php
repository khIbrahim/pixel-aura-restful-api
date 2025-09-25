<?php

namespace App\Traits\V1\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasAdvancedFiltering
{

    public function applyAdvancedFilters(array $filters, ?Builder $query = null): Builder
    {
        $query ??= $this->query();

        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (str_contains($key, '.')) {
                $this->applyNestedFilter($query, $key, $value);
                continue;
            }

            if (str_contains($key, '__')) {
                $this->applyOperatorFilter($query, $key, $value);
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($key, $value);
                continue;
            }

            if ($this->isDateField($key) && is_string($value) && str_contains($value, ',')) {
                $this->applyDateRangeFilter($query, $key, $value);
                continue;
            }

            $query->where($key, $value);
        }

        return $query;
    }

    protected function applyNestedFilter(Builder $query, string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $relation = array_shift($parts);
        $field = implode('.', $parts);

        $query->whereHas($relation, function ($q) use ($field, $value) {
            $q->where($field, $value);
        });
    }

    protected function applyOperatorFilter(Builder $query, string $key, mixed $value): void
    {
        [$field, $operator] = explode('__', $key, 2);

        match ($operator) {
            'gt', 'gte'     => $query->where($field, $operator === 'gt' ? '>' : '>=', $value),
            'lt', 'lte'     => $query->where($field, $operator === 'lt' ? '<' : '<=', $value),
            'ne', 'not'     => $query->where($field, '!=', $value),
            'like', 'ilike' => $query->where($field, 'LIKE', "%{$value}%"),
            'starts'        => $query->where($field, 'LIKE', "{$value}%"),
            'ends'          => $query->where($field, 'LIKE', "%{$value}"),
            'in'            => $query->whereIn($field, is_array($value) ? $value : explode(',', $value)),
            'notin'         => $query->whereNotIn($field, is_array($value) ? $value : explode(',', $value)),
            'null'          => $query->whereNull($field),
            'notnull'       => $query->whereNotNull($field),
            default         => $query->where($field, $value)
        };
    }

    protected function applyDateRangeFilter(Builder $query, string $field, string $value): void
    {
        $dates = explode(',', $value, 2);
        if (count($dates) === 2) {
            $query->whereBetween($field, $dates);
        }
    }

    protected function isDateField(string $field): bool
    {
        $dateFields = ['created_at', 'updated_at', 'deleted_at', 'published_at', 'expires_at'];
        return in_array($field, $dateFields) || Str::endsWith($field, '_at');
    }

    public function applySorting(Builder $query, array|string $sorts): Builder
    {
        if (is_string($sorts)) {
            $sorts = explode(',', $sorts);
        }

        foreach ($sorts as $sort) {
            $direction = 'asc';

            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            if (str_contains($sort, '.')) {
                $this->applyNestedSorting($query, $sort, $direction);
            } else {
                $query->orderBy($sort, $direction);
            }
        }

        return $query;
    }

    protected function applyNestedSorting(Builder $query, string $field, string $direction): void
    {
        $parts = explode('.', $field);
        $relation = array_shift($parts);
        $relationField = implode('.', $parts);

        $query->join(
            Str::snake($relation),
            $query->getModel()->getTable() . '.' . Str::snake($relation) . '_id',
            '=',
            Str::snake($relation) . '.id'
        )->orderBy(Str::snake($relation) . '.' . $relationField, $direction);
    }
}
