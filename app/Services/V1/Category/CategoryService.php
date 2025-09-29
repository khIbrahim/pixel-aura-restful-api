<?php

namespace App\Services\V1\Category;

use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\Contracts\V1\Category\CategoryServiceInterface;
use App\Contracts\V1\Shared\SkuGeneratorServiceInterface;
use App\DTO\V1\Category\CreateCategoryDTO;
use App\DTO\V1\Category\UpdateCategoryDTO;
use App\Events\V1\Category\CategoryCreated;
use App\Events\V1\Category\CategoryDeleted;
use App\Events\V1\Category\CategoryUpdated;
use App\Exceptions\V1\Category\CategoryCreationException;
use App\Exceptions\V1\Category\CategoryDeletionException;
use App\Exceptions\V1\Category\CategoryUpdateException;
use App\Exceptions\V1\Category\PositionDuplicateException;
use App\Models\V1\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface  $categoryRepository,
        private SkuGeneratorServiceInterface $skuGeneratorService
    ){}

    public function create(CreateCategoryDTO $data): Category
    {
        return DB::transaction(function () use ($data) {
            try {
                $sku = $this->skuGeneratorService->generate($data->name, Category::class, null, ['store_id' => $data->store_id]);

                if ($this->categoryRepository->skuExists($sku, $data->store_id)) {
                    throw CategoryCreationException::skuAlreadyExists($sku);
                }

                $position = $data->position !== null ? $data->position : $this->categoryRepository->getMaxPositionForStore($data->store_id) + 1;
                if ($this->categoryRepository->hasPositionCollision($position, $data->store_id)) {
                    throw CategoryCreationException::positionDuplicate($position);
                }

                if ($this->categoryRepository->find($data->parent_id) === null && $data->parent_id !== null) {
                    throw CategoryCreationException::invalidParent($data->parent_id);
                }

                $dto = new CreateCategoryDTO(
                    name: $data->name,
                    description: $data->description,
                    tags: $data->tags,
                    position: $position,
                    parent_id: $data->parent_id,
                    is_active: $data->is_active,
                    store_id: $data->store_id,
                    sku: $sku,
                );

                /** @var Category $category */
                $category = $this->categoryRepository->create($dto->toArray());
                broadcast(new CategoryCreated($category))->toOthers();

                return $category;
            } catch (Throwable $e) {
                if ($e instanceof CategoryCreationException) {
                    throw $e;
                }

                throw CategoryCreationException::default($e);
            }
        });
    }

    public function update(Category $category, UpdateCategoryDTO $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            try {
                $attributes = [];

                if ($data->is_active !== null && $data->is_active !== $category->is_active) {
                    if (! $data->is_active && $category->hasActiveItems()) {
                        throw CategoryUpdateException::hasActiveItems();
                    }

                    $attributes['is_active'] = (bool)$data->is_active;
                }

                if ($data->parent_id !== null && $data->parent_id !== $category->parent_id) {
                    if ($this->wouldCreateCycle($category, $data->parent_id)) {
                        throw CategoryUpdateException::cannotUpdateParent();
                    }
                    $attributes['parent_id'] = $data->parent_id;
                }

                if ($data->name !== null && $data->name !== $category->name) {
                    $attributes['name'] = $data->name;
                }

                if ($data->description !== null && $data->description !== $category->description) {
                    $attributes['description'] = $data->description;
                }

                if ($data->tags !== null && $data->tags !== $category->tags) {
                    $attributes['tags'] = $data->tags;
                }

                if ($data->position !== null && $data->position !== $category->position) {
                    $this->repositionSingle($category, $data->position);
                    $attributes['position'] = $data->position;
                }

                if (! empty($attributes)) {
                    $this->categoryRepository->update($category, $attributes);
                }

                $category = $category->refresh();
                broadcast(new CategoryUpdated($category))->toOthers();

                return $category;
            } catch (Throwable $e) {
                if ($e instanceof CategoryUpdateException) {
                    throw $e;
                }
                throw CategoryUpdateException::default($e->getMessage());
            }
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            try {
                if ($category->hasItems()){
                    throw CategoryDeletionException::hasItems();
                }

                if ($category->hasSubcategories()) {
                    throw CategoryDeletionException::hasSubcategories();
                }

                $deletedPosition = $category->position;
                $storeId         = $category->store_id;

                $this->categoryRepository->delete($category);
                $this->categoryRepository->shiftRangeDown($storeId, $deletedPosition + 1, PHP_INT_MAX);

                broadcast(new CategoryDeleted($category))->toOthers();
            } catch (Throwable $e) {
                if ($e instanceof CategoryDeletionException) {
                    throw $e;
                }

                throw CategoryDeletionException::default($e->getMessage());
            }
        });
    }

    public function toggleActivation(Category $category, bool $isActive): Category
    {
        if ($isActive === $category->is_active){
            return $category;
        }

        $this->categoryRepository->update($category, ['is_active' => $isActive]);
        return $category->refresh();
    }

    public function reorder(int $storeId, array $idPositionMap): void
    {
        if (empty($idPositionMap)) {
            return;
        }

        $positions = array_values($idPositionMap);
        if (count($positions) !== count(array_unique($positions))) {
            throw PositionDuplicateException::withPositions($positions);
        }

        DB::transaction(function () use ($storeId, $idPositionMap) {
            $this->categoryRepository->bulkSetPositions($idPositionMap, $storeId);
        });
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->categoryRepository->list($storeId, $filters, $perPage);
    }

    private function repositionSingle(Category $category, int $newPosition): void
    {
        $storeId     = $category->store_id;
        $oldPosition = $category->position;
        $max         = $this->categoryRepository->getMaxPositionForStore($storeId);
        $newPosition = max(1, min($newPosition, $max));
        if ($newPosition === $oldPosition) {
            return;
        }

        if ($newPosition < $oldPosition) {
            $this->categoryRepository->shiftRangeUp($storeId, $newPosition, $oldPosition - 1);
        } else {
            $this->categoryRepository->shiftRangeDown($storeId, $oldPosition + 1, $newPosition);
        }
    }

    private function wouldCreateCycle(Category $category, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return false;
        }

        if ($category->id === $newParentId) {
            return true;
        }

        $parent = $this->categoryRepository->find($newParentId);
        while ($parent !== null) {
            if ($parent->id === $category->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

}
