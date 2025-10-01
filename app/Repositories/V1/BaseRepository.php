<?php

namespace App\Repositories\V1;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->model());
    }

    abstract public function model(): string;

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->query()->select($columns)->find($id);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->query()->select($columns)->findOrFail($id);
    }

    public function findMany(array $ids, array $columns = ['*']): Collection
    {
        return $this->query()->select($columns)->findMany($ids);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->query()->select($columns)->where($field, $value)->first();
    }

    public function findManyBy(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->query()->select($columns)->where($field, $value)->get();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->select($columns)->get();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return $this->query()->select($columns)->paginate($perPage, $columns, $pageName, $page);
    }

    public function create(array $attributes): Model
    {
        return DB::transaction(function () use ($attributes) {
            return $this->query()->create($attributes);
        });
    }

    public function update(Model $model, array $attributes): Model
    {
        return DB::transaction(function () use ($model, $attributes) {
            $model->update(array_filter($attributes, fn($v) => $v !== null));
            return $model->fresh();
        });
    }

    public function updateWhere(array $conditions, array $attributes): int
    {
        return DB::transaction(function () use ($conditions, $attributes) {
            $query = $this->query();
            foreach ($conditions as $field => $value) {
                $query->where($field, $value);
            }
            return $query->update($attributes);
        });
    }

    public function delete(Model $model): bool
    {
        return DB::transaction(function () use ($model) {
            return $model->delete();
        });
    }

    public function deleteById(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->query()->where('id', $id)->delete() > 0;
        });
    }

    public function deleteWhere(array $conditions): int
    {
        return DB::transaction(function () use ($conditions) {
            $query = $this->query();
            foreach ($conditions as $field => $value) {
                $query->where($field, $value);
            }
            return $query->delete();
        });
    }

    public function exists(int $id): bool
    {
        return $this->query()->where('id', $id)->exists();
    }

    public function existsWhere(array $conditions): bool
    {
        $query = $this->query();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->exists();
    }

    public function count(): int
    {
        return $this->query()->count();
    }

    public function countWhere(array $conditions): int
    {
        $query = $this->query();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->count();
    }

    public function first(array $columns = ['*']): ?Model
    {
        return $this->query()->select($columns)->first();
    }

    public function firstOrFail(array $columns = ['*']): Model
    {
        return $this->query()->select($columns)->firstOrFail();
    }

    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return DB::transaction(function () use ($attributes, $values) {
            return $this->query()->firstOrCreate($attributes, $values);
        });
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return DB::transaction(function () use ($attributes, $values) {
            return $this->query()->updateOrCreate($attributes, $values);
        });
    }

    public function bulkInsert(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            return $this->query()->insert($data);
        });
    }

    public function bulkUpsert(array $values, array $uniqueBy, ?array $update = null): int
    {
        return DB::transaction(function () use ($values, $uniqueBy, $update) {
            return $this->query()->upsert($values, $uniqueBy, $update);
        });
    }

    public function applyFilters(array $filters): Builder
    {
        $query = $this->query();

        foreach ($filters as $filter => $value) {
            if (method_exists($this, 'scope' . ucfirst($filter))) {
                $query = $this->{'scope' . ucfirst($filter)}($query, $value);
            } elseif ($value !== null && $value !== '') {
                $query->where($filter, $value);
            }
        }

        return $query;
    }

    public function search(string $term, array $searchFields = ['name']): Builder
    {
        $query = $this->query();

        $query->where(function ($q) use ($term, $searchFields) {
            foreach ($searchFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$term}%");
            }
        });

        return $query;
    }

    public function withTrashed(): Builder
    {
        return $this->query()->withTrashed();
    }

    public function onlyTrashed(): Builder
    {
        return $this->query()->onlyTrashed();
    }

    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->query()->withTrashed()->find($id);
            if ($model && method_exists($model, 'restore')) {
                return $model->restore();
            } else {
                return false;
            }
        });
    }

    public function forceDelete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->query()->withTrashed()->find($id);
            if ($model) {
                return $model->forceDelete();
            } else {
                return false;
            }
        });
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function resetModel(): static
    {
        $this->model = app($this->model());
        return $this;
    }

    public function increment(Model $model, string $field, int $amount = 1): int
    {
        return DB::transaction(function () use ($model, $field, $amount){
            return $model->increment($field, $amount);
        });
    }

}
