<?php

namespace App\Services\V1\Item;

use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\DTO\V1\Option\OptionPivotDTO;
use App\DTO\V1\OptionList\OptionListPivotDTO;
use App\Models\V1\Item;

readonly class ItemAttachmentService implements ItemAttachmentServiceInterface
{
    public function __construct(
        private ItemRepositoryInterface $repository,
    ) {}

    public function attachIngredient(Item $item, array|IngredientPivotDTO $data): void
    {
        $this->repository->bulkAttachRelation(
            $item->id,
            'ingredients',
            is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]
        );
    }

    public function detachIngredient(Item $item, int $ingredientId): void
    {
        $this->repository->detachRelation($item->id, 'ingredients', $ingredientId);
    }

    public function attachOption(Item $item, array|OptionPivotDTO $data): void
    {
        $this->repository->bulkAttachRelation(
            $item->id,
            'options',
            is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]
        );
    }

    public function detachOption(Item $item, int $optionId): void
    {
        $this->repository->detachRelation($item->id, 'options', $optionId);
    }

    public function attachOptionList(Item $item, array|OptionListPivotDTO $data): void
    {
        $this->repository->bulkAttachRelation(
            $item->id,
            'optionLists',
            is_array($data) ? $data : [$data->getPivotKey() => $data->getPivotData()]
        );
    }

    public function detachOptionList(Item $item, int $optionListId): void
    {
        $this->repository->detachRelation($item->id, 'optionLists', $optionListId);
    }

    public function detachAllIngredients(Item $item): void
    {
        $item->ingredients()->detach();
    }

    public function detachAllOptions(Item $item): void
    {
        $item->options()->detach();
    }

    public function detachAllOptionLists(Item $item): void
    {
        $item->optionLists()->detach();
    }
}
