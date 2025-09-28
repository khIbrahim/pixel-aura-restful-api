<?php

namespace App\Contracts\V1\Category;

use App\DTO\V1\Category\CreateCategoryDTO;
use App\DTO\V1\Category\UpdateCategoryDTO;
use App\Exceptions\V1\Category\CategoryCreationException;
use App\Exceptions\V1\Category\CategoryDeletionException;
use App\Exceptions\V1\Category\CategoryUpdateException;
use App\Exceptions\V1\Category\PositionDuplicateException;
use App\Models\V1\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{

    /**
     * @throws CategoryCreationException
     */
    public function create(CreateCategoryDTO $data): Category;

    /**
     * @throws CategoryUpdateException
     */
    public function update(Category $category, UpdateCategoryDTO $data): Category;

    /**
     * @throws CategoryDeletionException
     */
    public function delete(Category $category): void;

    public function toggleActivation(Category $category, bool $isActive): Category;

    /**
     * @param array<int,int> $idPositionMap
     * @throws PositionDuplicateException
     */
    public function reorder(int $storeId, array $idPositionMap): void;

    /**
     * @param array $filters search,parent_id,is_active,with(array)
     */
    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

}
