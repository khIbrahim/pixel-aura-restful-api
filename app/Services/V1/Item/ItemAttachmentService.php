<?php

namespace App\Services\V1\Item;

use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\DTO\V1\Option\OptionPivotDTO;
use App\DTO\V1\OptionList\OptionListPivotDTO;
use App\Models\V1\Item;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class ItemAttachmentService implements ItemAttachmentServiceInterface
{

    public function __construct(
        private ItemRepositoryInterface $repository,
    ) {}

    public function attachIngredient(Item $item, array|IngredientPivotDTO $data): void
    {
        DB::transaction(function () use($item, $data){
            $this->repository->bulkAttachRelation($item->id, 'ingredients', is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]);
            $this->clearCache($item->store_id);
        });
    }

    public function detachIngredient(Item $item, int $ingredientId): void
    {
        DB::transaction(function () use ($item, $ingredientId){
            $this->repository->detachRelation($item->id, 'ingredients', $ingredientId);
            $this->clearCache($item->store_id);
        });
    }

    public function attachOption(Item $item, array|OptionPivotDTO $data): void
    {
        DB::transaction(function () use($item, $data){
            $this->repository->bulkAttachRelation($item->id, 'options', is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]);
            $this->clearCache($item->store_id);
        });
    }

    public function detachOption(Item $item, int $optionId): void
    {
        DB::transaction(function () use ($item, $optionId) {
            $this->repository->detachRelation($item->id, 'options', $optionId);
            $this->clearCache($item->store_id);
        });
    }

    public function attachOptionList(Item $item, array|OptionListPivotDTO $data): void
    {
        DB::transaction(function () use($item, $data){
            $this->repository->bulkAttachRelation($item->id, 'optionLists', is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]);
            $this->clearCache($item->store_id);
        });
    }

    public function detachOptionList(Item $item, int $optionListId): void
    {
        DB::transaction(function () use ($item, $optionListId) {
            $this->repository->detachRelation($item->id, 'optionLists', $optionListId);
            $this->clearCache($item->store_id);
        });
    }

    private function clearCache(int $storeId): void
    {
        Cache::tags(['items'])->flush();
        Cache::forget('items.store.' . $storeId);
    }
}
