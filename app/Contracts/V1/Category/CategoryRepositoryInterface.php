<?php

namespace App\Contracts\V1\Category;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\Models\V1\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{

    public function getMaxPositionForStore(int $storeId): int;

    public function hasPositionCollision(int $position, int $storeId): bool;

    public function incrementPositionsFrom(int $position, int $storeId): void;

    public function skuExists(string $sku, int $storeId, ?int $ignoreId = null): bool;

    public function findBySku(string $sku, int $storeId): ?Category;

    public function shiftRangeUp(int $storeId, int $fromInclusive, int $toInclusive): void;

    public function shiftRangeDown(int $storeId, int $fromInclusive, int $toInclusive): void;

    public function bulkSetPositions(array $idPositionMap, int $storeId): void; // id => position

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
