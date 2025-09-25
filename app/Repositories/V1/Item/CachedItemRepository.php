<?php

namespace App\Repositories\V1\Item;

use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\CreateVariantDTO;
use App\DTO\V1\Option\CreateOptionDTO;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Option;
use App\Repositories\V1\BaseRepository;
use App\Traits\V1\Repository\CacheableRepositoryTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CachedItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    use CacheableRepositoryTrait;

    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
    ){}

    public function createItem(CreateItemDTO $data): Item
    {
        $item = $this->itemRepository->createItem($data);

        $this->invalidateItemCaches($item->store_id, $item->category_id);

        return $item;
    }

    public function findItem(int $id, bool $withRelations = true): ?Item
    {
        $key = $withRelations ? "item:full:$id" : "item:basic:$id";

        return $this->remember($key, fn() => $this->itemRepository->findItem($id, $withRelations), [$this->getTag(), "store.items"]);
    }

    public function getItemsByCategory(int $categoryId): Collection
    {
        $key = "items:category:$categoryId";

        return $this->remember($key, function() use ($categoryId) {
            return $this->itemRepository->getItemsByCategory($categoryId);
        }, [$this->getTag(), "category:$categoryId"]);
    }

    public function bulkCreateVariants(Item $item, array $variants): void
    {
        $this->itemRepository->bulkCreateVariants($item, $variants);

        $this->invalidateItemSpecific($item->id, $item->store_id);
    }

    public function attachIngredient(Item $item, Ingredient $ingredient, CreateIngredientDTO $data): void
    {
        $this->itemRepository->attachIngredient($item, $ingredient, $data);
        $this->invalidateItemSpecific($item->id, $item->store_id);
    }

    public function attachOption(Item $item, Option $option, CreateOptionDTO $data): void
    {
        $this->itemRepository->attachOption($item, $option, $data);
        $this->invalidateItemSpecific($item->id, $item->store_id);
    }

    private function invalidateItemCaches(int $storeId, ?int $categoryId = null): void
    {
        $keys = [
            "items:store:$storeId",
            "ingredients:store:$storeId",
            "options:store:$storeId"
        ];

        if ($categoryId) {
            $keys[] = "items:category:$categoryId";
        }

        $this->invalidate($storeId, $keys);
    }

    private function invalidateItemSpecific(int $itemId, int $storeId): void
    {
        $keys = [
            "item:basic:$itemId",
            "item:full:$itemId"
        ];

        $this->invalidate($storeId, $keys);
    }

    protected function getTag(): string
    {
        return 'items';
    }

    public function createVariant(Item $item, CreateVariantDTO $data): ItemVariant
    {
        $variant = $this->itemRepository->createVariant($item, $data);
        $this->invalidateItemSpecific($item->id, $item->store_id);
        return $variant;
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $key = "store:$storeId:items:" . md5(serialize($filters)) . ":page:$perPage";
        return $this->remember($key, fn() => $this->itemRepository->list($storeId, $filters, $perPage), ["store:$storeId", $this->getTag()]);
    }

    public function attachOptions(Item $item, array $options): Collection
    {
        $attachedOptions = $this->itemRepository->attachOptions($item, $options);
        $this->invalidateItemSpecific($item->id, $item->store_id);
        return $attachedOptions;
    }

    public function model(): string
    {
        return Item::class;
    }

}
