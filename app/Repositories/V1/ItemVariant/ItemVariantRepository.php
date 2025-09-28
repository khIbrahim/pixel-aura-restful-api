<?php

namespace App\Repositories\V1\ItemVariant;

use App\Contracts\V1\ItemVariant\ItemVariantRepositoryInterface;
use App\DTO\V1\ItemVariant\CreateItemVariantDTO;
use App\DTO\V1\ItemVariant\UpdateItemVariantDTO;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Repositories\V1\BaseRepository;
use App\Traits\V1\Repository\CacheableRepositoryTrait;
use App\Traits\V1\Repository\HasAdvancedFiltering;
use App\Traits\V1\Repository\HasBatchOperations;
use Illuminate\Support\Collection;

class ItemVariantRepository extends BaseRepository implements ItemVariantRepositoryInterface
{
    use CacheableRepositoryTrait, HasAdvancedFiltering, HasBatchOperations;

    public function getByItem(Item $item): Collection
    {
        return $item->variants()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function model(): string
    {
        return ItemVariant::class;
    }

    protected function getTag(): string
    {
        return 'item_variants';
    }

    public function createVariant(Item $item, CreateItemVariantDTO $data): ItemVariant
    {
        return $item->variants()->create($data->toArray());
    }

    public function bulkCreateVariants(Item $item, array $variants): void
    {
        $rows = [];

        /** @var CreateItemVariantDTO $variant */
        foreach ($variants as $variant) {
            $rows[] = array_merge($variant->toArray(), ['item_id' => $item->id, 'store_id' => $item->store_id, 'created_at' => now(), 'updated_at' => now()]);
        }

        ItemVariant::query()->insert($rows);

        $item->load('variants');
    }

    public function updateVariant(ItemVariant $itemVariant, UpdateItemVariantDTO $data): ItemVariant
    {
        $itemVariant->update(
            collect($data->toArray())->except(['item_id', 'store_id', 'id'])->toArray()
        );

        return $itemVariant;
    }

    public function deleteVariant(ItemVariant $itemVariant, ?Item $item = null): void
    {
        $itemVariant->delete();

        $item?->load('variants');
    }

    public function deleteAllByItem(Item $item): void
    {
        $item->variants()->delete();
    }
}
