<?php

namespace App\Contracts\V1\Category;

use App\DTO\V1\Option\OptionPivotDTO;
use App\Models\V1\Category;

interface CategoryOptionListsServiceInterface
{

    public function attach(Category $category, array|OptionPivotDTO $data): void;

    public function detach(Category $category, int $id): void;

    public function detachAll(Category $category): void;

}
