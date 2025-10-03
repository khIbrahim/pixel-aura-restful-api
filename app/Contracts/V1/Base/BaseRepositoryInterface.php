<?php

namespace App\Contracts\V1\Base;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get a new query builder for the model's table
     */
    public function query(): Builder;

    /**
     * Find a model by its primary key
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find a model by its primary key or throw an exception
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find multiple models by their primary keys
     */
    public function findMany(array $ids, array $columns = ['*']): Collection;

    /**
     * Find a model by a specific field
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Find multiple models by a specific field
     */
    public function findManyBy(string $field, mixed $value, array $columns = ['*']): Collection;

    public function findByDate(string $field, string $date, array $columns = ['*']): Collection;

    /**
     * Get all models
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginate the given query
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator;

    /**
     * Create a new model
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing model
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Update models where conditions match
     */
    public function updateWhere(array $conditions, array $attributes): int;

    /**
     * Delete a model
     */
    public function delete(Model $model): bool;

    /**
     * Delete a model by its primary key
     */
    public function deleteById(int $id): bool;

    /**
     * Delete models where conditions match
     */
    public function deleteWhere(array $conditions): int;

    /**
     * Check if a model exists by its primary key
     */
    public function exists(int $id): bool;

    /**
     * Check if a model exists where conditions match
     */
    public function existsWhere(array $conditions): bool;

    /**
     * Get the count of all models
     */
    public function count(): int;

    /**
     * Get the count of models where conditions match
     */
    public function countWhere(array $conditions): int;

    /**
     * Get the first model
     */
    public function first(array $columns = ['*']): ?Model;

    /**
     * Get the first model or throw an exception
     */
    public function firstOrFail(array $columns = ['*']): Model;

    /**
     * Get the first model or create it if it doesn't exist
     */
    public function firstOrCreate(array $attributes, array $values = []): Model;

    /**
     * Update or create a model
     */
    public function updateOrCreate(array $attributes, array $values = []): Model;

    /**
     * Bulk insert data
     */
    public function bulkInsert(array $data): bool;

    /**
     * Bulk upsert data
     */
    public function bulkUpsert(array $values, array $uniqueBy, ?array $update = null): int;

    /**
     * Apply filters to the query
     */
    public function applyFilters(array $filters): Builder;

    /**
     * Search models by term in specified fields
     */
    public function search(string $term, array $searchFields = ['name']): Builder;

    /**
     * Include trashed models in the query
     */
    public function withTrashed(): Builder;

    /**
     * Get only trashed models
     */
    public function onlyTrashed(): Builder;

    /**
     * Restore a soft-deleted model
     */
    public function restore(int $id): bool;

    /**
     * Force delete a model
     */
    public function forceDelete(int $id): bool;

    /**
     * Get the underlying model instance
     */
    public function getModel(): Model;

    /**
     * Reset the model instance
     */
    public function resetModel(): static;
}
