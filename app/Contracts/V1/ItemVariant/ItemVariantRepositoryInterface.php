<?php

namespace App\Contracts\V1\ItemVariant;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\DTO\V1\ItemVariant\CreateItemVariantDTO;
use App\DTO\V1\ItemVariant\UpdateItemVariantDTO;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use Illuminate\Support\Collection;

interface ItemVariantRepositoryInterface extends BaseRepositoryInterface
{
    public function getByItem(Item $item): Collection;

    public function createVariant(Item $item, CreateItemVariantDTO $data): ItemVariant;

    public function bulkCreateVariants(Item $item, array $variants): void;

    public function updateVariant(ItemVariant $itemVariant, UpdateItemVariantDTO $data): ItemVariant;

    public function deleteVariant(ItemVariant $itemVariant, ?Item $item = null): void;

    public function deleteAllByItem(Item $item): void;
}
