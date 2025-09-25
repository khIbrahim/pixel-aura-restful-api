<?php

namespace App\Traits\V1\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

trait ManagesRelations
{
    /**
     * Eager load relations to prevent N+1 queries
     */
    public function with(array|string $relations): Builder
    {
        return $this->query()->with($relations);
    }

    /**
     * Load relations with constraints
     */
    public function withWhereHas(string $relation, callable $callback = null): Builder
    {
        return $this->query()->whereHas($relation, $callback)->with([$relation => $callback]);
    }

    /**
     * Count related models
     */
    public function withCount(array|string $relations): Builder
    {
        return $this->query()->withCount($relations);
    }

    /**
     * Load relations conditionally
     */
    public function withWhen(bool $condition, array|string $relations): Builder
    {
        return $this->query()->when($condition, fn($query) => $query->with($relations));
    }

    /**
     * Sync many-to-many relationships
     */
    public function syncRelation(int $modelId, string $relation, array $ids, bool $detaching = true): array
    {
        $model = $this->findOrFail($modelId);
        return $model->{$relation}()->sync($ids, $detaching);
    }

    /**
     * Attach to many-to-many relationship
     */
    public function attachRelation(int $modelId, string $relation, array|int $ids, array $attributes = []): void
    {
        $model = $this->findOrFail($modelId);
        $model->{$relation}()->attach($ids, $attributes);
    }

    /**
     * Detach from many-to-many relationship
     */
    public function detachRelation(int $modelId, string $relation, array|int $ids = []): int
    {
        $model = $this->findOrFail($modelId);
        return $model->{$relation}()->detach($ids);
    }

    /**
     * Update pivot table data
     */
    public function updatePivot(int $modelId, string $relation, array|int $relatedIds, array $attributes): int
    {
        $model = $this->findOrFail($modelId);
        return $model->{$relation}()->updateExistingPivot($relatedIds, $attributes);
    }
}
