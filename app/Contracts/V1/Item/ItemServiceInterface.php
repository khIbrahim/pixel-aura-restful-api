<?php

namespace App\Contracts\V1\Item;

use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\UpdateItemDTO;
use App\Models\V1\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemServiceInterface
{
    public function create(CreateItemDTO $data): Item;

    public function update(UpdateItemDTO $data, Item $item): Item;

    public function delete(Item $item): bool;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
