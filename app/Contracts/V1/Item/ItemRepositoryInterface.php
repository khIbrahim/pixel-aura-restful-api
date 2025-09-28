<?php

namespace App\Contracts\V1\Item;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\Models\V1\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ItemRepositoryInterface extends BaseRepositoryInterface
{
    public function getItemsByCategory(int $categoryId): Collection;

    public function findItem(int $id, bool $withRelations = true): ?Item;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
