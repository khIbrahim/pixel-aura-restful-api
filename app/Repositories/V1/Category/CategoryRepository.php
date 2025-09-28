<?php

namespace App\Repositories\V1\Category;

use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\Models\V1\Category;
use App\Repositories\V1\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Cache;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{

    private const int CACHE_TTL = 600;

    public function getMaxPositionForStore(int $storeId): int
    {
        $cacheKey = "store:$storeId:category:max_position";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId) {
            return (int) Category::query()->where('store_id', $storeId)->max('position');
        });
    }

    public function hasPositionCollision(int $position, int $storeId): bool
    {
        return Category::query()
            ->where('store_id', $storeId)
            ->where('position', $position)
            ->exists();
    }

    public function incrementPositionsFrom(int $position, int $storeId): void
    {
        Category::query()
            ->where('store_id', $storeId)
            ->where('position', '>=', $position)
            ->increment('position');

        $cacheKey = "store:$storeId:category:max_position";
        Cache::forget($cacheKey);
    }

    public function skuExists(string $sku, int $storeId, ?int $ignoreId = null): bool
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->where('sku', $sku)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function findBySku(string $sku, int $storeId): ?Category
    {
        $cacheKey = "store:$storeId:category:sku:$sku";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sku, $storeId) {
            return Category::query()->where('sku', $sku)->where('store_id', $storeId)->first();
        });
    }

    public function shiftRangeUp(int $storeId, int $fromInclusive, int $toInclusive): void
    {
        if ($fromInclusive > $toInclusive) {
            return;
        }

        Category::query()
            ->where('store_id', $storeId)
            ->whereBetween('position', [$fromInclusive, $toInclusive])
            ->increment('position');

        $cacheKey = "store:$storeId:category:max_position";
        Cache::forget($cacheKey);
    }

    public function shiftRangeDown(int $storeId, int $fromInclusive, int $toInclusive): void
    {
        if ($fromInclusive > $toInclusive) {
            return;
        }

        Category::query()
            ->where('store_id', $storeId)
            ->whereBetween('position', [$fromInclusive, $toInclusive])
            ->decrement('position');

        $cacheKey = "store:$storeId:category:max_position";
        Cache::forget($cacheKey);
    }

    public function bulkSetPositions(array $idPositionMap, int $storeId): void
    {
        if (empty($idPositionMap)) {
            return;
        }
        $cases = [];
        $ids   = [];

        foreach ($idPositionMap as $id => $pos) {
            $id      = (int) $id; $pos = (int) $pos;
            $ids[]   = $id;
            $cases[] = "WHEN $id THEN $pos";
        }

        $caseSql = 'CASE id ' . implode(' ', $cases) . ' END';
        Category::query()
            ->where('store_id', $storeId)
            ->whereIn('id', $ids)
            ->update(['position' => new Expression($caseSql)]);

        $cacheKey = "store:$storeId:category:max_position";
        Cache::forget($cacheKey);
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Category::query()->where('store_id', $storeId)->orderBy('position');

        if(! empty($filters['search'])){
            $s = (string) $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhereJsonContains('tags', $s);
            });
        }

        if(array_key_exists('is_active', $filters) && $filters['is_active'] !== null){
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (array_key_exists('parent_id', $filters) && $filters['parent_id'] !== null) {
            $query->where('parent_id', (int) $filters['parent_id']);
        }

        if (! empty($filters['with'])) {
            $with    = is_string($filters['with']) ? explode(',', $filters['with']) : $filters['with'];
            $allowed = ['children', 'parent'];
            $query->with(array_intersect($allowed, $with));
        }

        return $query->paginate($perPage);
    }

    public function model(): string
    {
        return Category::class;
    }
}
