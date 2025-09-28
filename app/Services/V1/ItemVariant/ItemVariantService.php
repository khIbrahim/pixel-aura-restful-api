<?php

namespace App\Services\V1\ItemVariant;

use App\Contracts\V1\ItemVariant\ItemVariantRepositoryInterface;
use App\Contracts\V1\ItemVariant\ItemVariantServiceInterface;
use App\DTO\V1\ItemVariant\CreateItemVariantDTO;
use App\DTO\V1\ItemVariant\UpdateItemVariantDTO;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use Illuminate\Support\Collection;

readonly class ItemVariantService implements ItemVariantServiceInterface
{
    public function __construct(
        private ItemVariantRepositoryInterface $repository
    ) {}

    public function getVariantsByItem(Item $item): Collection
    {
        return $this->repository->getByItem($item);
    }

    public function createVariant(Item $item, CreateItemVariantDTO $data): ItemVariant
    {
        return $this->repository->createVariant($item, $data);
    }

    public function bulkCreateVariants(Item $item, array $variants): void
    {
        $this->repository->bulkCreateVariants($item, $variants);
    }

    public function updateVariant(ItemVariant $itemVariant, UpdateItemVariantDTO $data): ItemVariant
    {
        return $this->repository->updateVariant($itemVariant, $data);
    }

    public function deleteVariant(ItemVariant $itemVariant, ?Item $item = null): void
    {
        $this->repository->deleteVariant($itemVariant, $item);
    }

    public function toggleVariantActive(ItemVariant $itemVariant): ItemVariant
    {
        /** @var ItemVariant $itemVariant */
        $itemVariant = $this->repository->update($itemVariant, [
            'is_active' => ! $itemVariant->is_active,
        ]);

        return $itemVariant->fresh();
    }

    public function deleteAllVariants(Item $item): void
    {
        $this->repository->deleteAllByItem($item);
    }
}
