<?php

namespace App\Contracts\V1\Category;

use App\DTO\V1\Category\CreateCategoryDTO;
use App\DTO\V1\Category\UpdateCategoryDTO;
use App\Exceptions\V1\Category\CategorySlugAlreadyExistsException;
use App\Exceptions\V1\Category\PositionDuplicateException;
use App\Models\V1\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{

    /**
     * @throws CategorySlugAlreadyExistsException
     */
    public function create(CreateCategoryDTO $data): Category;

    /**
     * @throws CategorySlugAlreadyExistsException
     */
    public function update(Category $category, UpdateCategoryDTO $data): Category;

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
