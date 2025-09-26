<?php

namespace App\Traits\V1\Repository;

use Illuminate\Database\Eloquent\Builder;

trait ManagesRelations
{

    public function with(array|string $relations): Builder
    {
        return $this->query()->with($relations);
    }

    public function withWhereHas(string $relation, ?callable $callback = null): Builder
    {
        return $this->query()->whereHas($relation, $callback)->with([$relation => $callback]);
    }

    public function withCount(array|string $relations): Builder
    {
        return $this->query()->withCount($relations);
    }

    public function withWhen(bool $condition, array|string $relations): Builder
    {
        return $this->query()->when($condition, fn($query) => $query->with($relations));
    }

    public function syncRelation(int $modelId, string $relation, array $ids, bool $detaching = true): array
    {
        $model = $this->findOrFail($modelId);
        return $model->{$relation}()->sync($ids, $detaching);
    }

    public function attachRelation(int $modelId, string $relation, array|int $ids, array $attributes = []): void
    {
        $model = $this->findOrFail($modelId);
        $model->{$relation}()->attach($ids, $attributes);
    }

    public function bulkAttachRelation(int $modelId, string $relation, array $data): void
    {
        $model = $this->findOrFail($modelId);
        $model->{$relation}()->syncWithoutDetaching($data);
    }

    public function detachRelation(int $modelId, string $relation, array|int $ids = []): int
    {
        $model = $this->findOrFail($modelId);
        return $model->{$relation}()->detach($ids);
    }

    public function updatePivot(int $modelId, string $relation, array|int $relatedIds, array $attributes): int
    {
        $model = $this->findOrFail($modelId);
        return $model->{$relation}()->updateExistingPivot($relatedIds, $attributes);
    }
}
