<?php

namespace App\Contracts\V1\Item;

use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\UpdateItemDTO;
use App\Exceptions\V1\Item\ItemCreationException;
use App\Exceptions\V1\Item\ItemDeletionException;
use App\Exceptions\V1\Item\ItemUpdateException;
use App\Models\V1\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemServiceInterface
{

    /**
     * @throws ItemCreationException
     */
    public function create(CreateItemDTO $data): Item;

    /**
     * @throws ItemUpdateException
     */
    public function update(UpdateItemDTO $data, Item $item): Item;

    /**
     * @throws ItemDeletionException
     */
    public function delete(Item $item): bool;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
