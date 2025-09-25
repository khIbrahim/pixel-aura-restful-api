<?php

namespace App\Contracts\V1\Category;

use App\DTO\V1\Category\CreateCategoryDTO;
use App\Models\V1\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{

    // Création
    public function create(CreateCategoryDTO $data): Category;

    public function getMaxPositionForStore(int $storeId): int;

    public function hasPositionCollision(int $position, int $storeId): bool;

    public function incrementPositionsFrom(int $position, int $storeId): void;

    public function slugExists(string $slug, int $storeId, ?int $ignoreId = null): bool;

    public function findBySlug(string $slug, int $storeId): ?Category;

    public function findById(int $id): ?Category;

    public function update(Category $category, array $attributes): Category;

    public function shiftRangeUp(int $storeId, int $fromInclusive, int $toInclusive): void;

    public function shiftRangeDown(int $storeId, int $fromInclusive, int $toInclusive): void;

    public function bulkSetPositions(array $idPositionMap, int $storeId): void; // id => position

    public function delete(Category $category): void;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
