<?php

namespace App\Services\V1\Category;

use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\Contracts\V1\Category\CategoryServiceInterface;
use App\Contracts\V1\Media\MediaManagerInterface;
use App\DTO\V1\Category\CreateCategoryDTO;
use App\DTO\V1\Category\UpdateCategoryDTO;
use App\DTO\V1\Media\MediaUploadOptions;
use App\Events\V1\Category\CategoryCreated;
use App\Events\V1\Category\CategoryDeleted;
use App\Events\V1\Category\CategoryUpdated;
use App\Exceptions\V1\Category\CategorySlugAlreadyExistsException;
use App\Exceptions\V1\Category\PositionDuplicateException;
use App\Models\V1\Category;
use App\Support\Results\MediaResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

readonly class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private MediaManagerInterface $mediaManager,
    ){}

    public function create(CreateCategoryDTO $data): Category
    {
        return DB::transaction(function () use ($data) {
            $slug = str()->slug($data->name);

            if ($this->categoryRepository->slugExists($slug, $data->store_id)) {
                throw new CategorySlugAlreadyExistsException();
            }

            $position = $this->resolveInsertionPosition($data->store_id, $data->position);

            if ($data->position !== null) {
                $this->categoryRepository->incrementPositionsFrom($position, $data->store_id);
            }

            $dto = new CreateCategoryDTO(
                name: $data->name,
                description: $data->description,
                tags: $data->tags,
                position: $position,
                parent_id: $data->parent_id,
                is_active: $data->is_active,
                store_id: $data->store_id,
                slug: $slug,
            );

            $category = $this->categoryRepository->create($dto);
            broadcast(new CategoryCreated($category))->toOthers();
            return $category;
        });
    }

    public function update(Category $category, UpdateCategoryDTO $data): Category
    {
        return DB::transaction(function () use ($category, $data){
            $attributes = [];

            if ($data->name !== null && $data->name !== $category->name){
                $slug = str()->slug($data->name);
                if ($this->categoryRepository->slugExists($slug, $category->store_id, $category->id)){
                    throw new CategorySlugAlreadyExistsException();
                }

                $attributes['name'] = $data->name;
                $attributes['slug'] = $slug;
            }

            if ($data->is_active !== null && $data->is_active !== $category->is_active){
                $attributes['is_active'] = (bool) $data->is_active;
            }

            if ($data->parent_id !== null && $data->parent_id !== $category->parent_id){
                $attributes['parent_id'] = $data->parent_id;
            }

            if($data->description !== null && $data->description !== $category->description){
                $attributes['description'] = $data->description;
            }

            if($data->tags !== null && $data->tags !== $category->tags){
                $attributes['tags'] = $data->tags;
            }

            if ($data->position !== null && $data->position !== $category->position){
                $this->repositionSingle($category, $data->position);
                $attributes['position'] = $data->position;
            }

            if (! empty($attributes)){
                $this->categoryRepository->update($category, $attributes);
            }

            $category = $category->refresh();
            broadcast(new CategoryUpdated($category))->toOthers();

            return $category;
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $deletedPosition = $category->position;
            $storeId         = $category->store_id;

            $this->categoryRepository->delete($category);
            $this->categoryRepository->shiftRangeDown($storeId, $deletedPosition + 1, PHP_INT_MAX);

            broadcast(new CategoryDeleted($category))->toOthers();
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
            throw new PositionDuplicateException('Positions dupliquées.');
        }

        DB::transaction(function () use ($storeId, $idPositionMap) {
            $this->categoryRepository->bulkSetPositions($idPositionMap, $storeId);
        });
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->categoryRepository->list($storeId, $filters, $perPage);
    }

    private function resolveInsertionPosition(int $storeId, ?int $requested): int
    {
        if ($requested === null) {
            return $this->categoryRepository->getMaxPositionForStore($storeId) + 1;
        }

        return max(1, $requested);
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

    public function uploadImage(Category $category, UploadedFile|string $file, string $type): MediaResult
    {
        $options = MediaUploadOptions::fromCategoryImage();

        return $this->mediaManager->replaceImage($category, $file, $options);
    }

    public function deleteImage(Category $category, ?int $mediaId = null): void
    {
        $this->mediaManager->deleteImage($category, 'category_images', $mediaId);
    }
}
