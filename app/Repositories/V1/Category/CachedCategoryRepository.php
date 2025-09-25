<?php

namespace App\Repositories\V1\Category;

use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\DTO\V1\Category\CreateCategoryDTO;
use App\Models\V1\Category;
use App\Traits\V1\Repository\CacheableRepositoryTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CachedCategoryRepository implements CategoryRepositoryInterface
{
    use CacheableRepositoryTrait;

    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    protected function getTag(): string
    {
        return 'categories';
    }

    public function create(CreateCategoryDTO $data): Category
    {
        $category = $this->categoryRepository->create($data);
        $this->invalidate($category->store_id, ["store:$category->store_id:category:max_position"]);
        return $category;
    }

    public function getMaxPositionForStore(int $storeId): int
    {
        $key = "store:$storeId:category:max_position";
        return $this->remember($key, fn() => $this->categoryRepository->getMaxPositionForStore($storeId), ["store:$storeId", $this->getTag()]);
    }

    public function findBySlug(string $slug, int $storeId): ?Category
    {
        $key = "store:$storeId:category:slug:$slug";
        return $this->remember($key, fn() => $this->categoryRepository->findBySlug($slug, $storeId), ["store:$storeId", $this->getTag()]);
    }

    public function findById(int $id): ?Category
    {
        $key = "category:id:$id";
        return $this->remember($key, fn() => $this->categoryRepository->findById($id), [$this->getTag()]);
    }

    public function update(Category $category, array $attributes): Category
    {
        $updated = $this->categoryRepository->update($category, $attributes);
        $this->invalidate($category->store_id, ["store:$category->store_id:category:max_position"]);
        return $updated;
    }

    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
        $this->invalidate($category->store_id, ["store:$category->store_id:category:max_position"]);
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $key = "store:$storeId:categories:" . md5(serialize($filters)) . ":page:$perPage";
        return $this->remember($key, fn() => $this->categoryRepository->list($storeId, $filters, $perPage), ["store:$storeId", $this->getTag()]);
    }

    public function slugExists(string $slug, int $storeId, ?int $ignoreId = null): bool
    {
        return $this->categoryRepository->slugExists($slug, $storeId, $ignoreId);
    }

    public function hasPositionCollision(int $position, int $storeId): bool
    {
        return $this->categoryRepository->hasPositionCollision($position, $storeId);
    }

    public function incrementPositionsFrom(int $position, int $storeId): void
    {
        $this->categoryRepository->incrementPositionsFrom($position, $storeId);
        $this->invalidate($storeId, ["store:$storeId:category:max_position"]);
    }

    public function shiftRangeUp(int $storeId, int $fromInclusive, int $toInclusive): void
    {
        $this->categoryRepository->shiftRangeUp($storeId, $fromInclusive, $toInclusive);
        $this->invalidate($storeId, ["store:$storeId:category:max_position"]);
    }

    public function shiftRangeDown(int $storeId, int $fromInclusive, int $toInclusive): void
    {
        $this->categoryRepository->shiftRangeDown($storeId, $fromInclusive, $toInclusive);
        $this->invalidate($storeId, ["store:$storeId:category:max_position"]);
    }

    public function bulkSetPositions(array $idPositionMap, int $storeId): void
    {
        $this->categoryRepository->bulkSetPositions($idPositionMap, $storeId);
        $this->invalidate($storeId, ["store:$storeId:category:max_position"]);
    }
}
