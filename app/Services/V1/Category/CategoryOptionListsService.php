<?php

namespace App\Services\V1\Category;

use App\Contracts\V1\Category\CategoryOptionListsServiceInterface;
use App\Contracts\V1\Category\CategoryRepositoryInterface;
use App\DTO\V1\Option\OptionPivotDTO;
use App\Models\V1\Category;

readonly class CategoryOptionListsService implements CategoryOptionListsServiceInterface
{

    public function __construct(
        private CategoryRepositoryInterface $repository
    ){}

    public function attach(Category $category, array|OptionPivotDTO $data): void
    {
        $this->repository->bulkAttachRelation($category->id, 'optionLists', is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]);
    }

    public function detach(Category $category, int $id): void
    {
        $this->repository->detachRelation($category->id, 'optionLists', $id);
    }

    public function detachAll(Category $category): void
    {
        $category->optionLists()->detach();
    }

}
