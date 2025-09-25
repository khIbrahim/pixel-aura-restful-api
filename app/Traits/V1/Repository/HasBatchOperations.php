<?php

namespace App\Traits\V1\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

trait HasBatchOperations
{
    /**
     * Batch create with chunking for large datasets
     */
    public function batchCreate(array $data, int $chunkSize = 500): bool
    {
        return DB::transaction(function () use ($data, $chunkSize) {
            $chunks = array_chunk($data, $chunkSize);

            foreach ($chunks as $chunk) {
                $this->query()->insert($chunk);
            }

            return true;
        });
    }

    /**
     * Batch update with chunking
     */
    public function batchUpdate(array $conditions, array $updates, int $chunkSize = 1000): int
    {
        return DB::transaction(function () use ($conditions, $updates, $chunkSize) {
            $totalUpdated = 0;

            $this->query()->where($conditions)
                ->chunkById($chunkSize, function (Collection $models) use ($updates, &$totalUpdated) {
                    $ids = $models->pluck('id')->toArray();
                    $updated = $this->query()->whereIn('id', $ids)->update($updates);
                    $totalUpdated += $updated;
                });

            return $totalUpdated;
        });
    }

    /**
     * Batch delete with chunking
     */
    public function batchDelete(array $conditions, int $chunkSize = 1000): int
    {
        return DB::transaction(function () use ($conditions, $chunkSize) {
            $totalDeleted = 0;

            $this->query()->where($conditions)
                ->chunkById($chunkSize, function (Collection $models) use (&$totalDeleted) {
                    $ids = $models->pluck('id')->toArray();
                    $deleted = $this->query()->whereIn('id', $ids)->delete();
                    $totalDeleted += $deleted;
                });

            return $totalDeleted;
        });
    }

    /**
     * Process large datasets with lazy collection to reduce memory usage
     */
    public function processLargeDataset(callable $callback, int $chunkSize = 1000): void
    {
        $this->query()->lazy($chunkSize)->each($callback);
    }

    /**
     * Bulk upsert with conflict resolution
     */
    public function bulkUpsertWithConflict(
        array $values,
        array $uniqueBy,
        ?array $update = null,
        int $chunkSize = 500
    ): int {
        return DB::transaction(function () use ($values, $uniqueBy, $update, $chunkSize) {
            $totalUpserted = 0;
            $chunks = array_chunk($values, $chunkSize);

            foreach ($chunks as $chunk) {
                $upserted = $this->query()->upsert($chunk, $uniqueBy, $update);
                $totalUpserted += $upserted;
            }

            return $totalUpserted;
        });
    }

    /**
     * Efficient exists check for multiple IDs
     */
    public function existsMany(array $ids): array
    {
        $existing = $this->query()->whereIn('id', $ids)->pluck('id')->toArray();
        return array_combine($ids, array_map(fn($id) => in_array($id, $existing), $ids));
    }

    /**
     * Get models by IDs while preserving order
     */
    public function findManyOrdered(array $ids, array $columns = ['*']): Collection
    {
        $models = $this->query()->whereIn('id', $ids)->get($columns)->keyBy('id');

        return collect($ids)->map(fn($id) => $models->get($id))->filter();
    }

    /**
     * Count models efficiently using database optimization
     */
    public function fastCount(array $conditions = []): int
    {
        $query = $this->query();

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        // Use more efficient counting for large tables
        return DB::selectOne(
            "SELECT COUNT(*) as count FROM ({$query->toSql()}) as sub",
            $query->getBindings()
        )->count;
    }
}
