<?php

namespace App\Contracts\V1\Item;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\DTO\V1\Item\CreateVariantDTO;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ItemRepositoryInterface extends BaseRepositoryInterface
{

    public function createVariant(Item $item, CreateVariantDTO $data): ItemVariant;

    public function bulkCreateVariants(Item $item, array $variants): void;

    public function getItemsByCategory(int $categoryId): Collection;

    public function findItem(int $id, bool $withRelations = true): ?Item;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;


}
