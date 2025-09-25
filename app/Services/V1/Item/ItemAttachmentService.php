<?php

namespace App\Services\V1\Item;

use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\Option;
use App\Traits\V1\Repository\ManagesPivotRelations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly class ItemAttachmentService implements ItemAttachmentServiceInterface
{
    use ManagesPivotRelations;

    private const array OPTION_PIVOT_COLUMNS = [
        'store_id',
        'name',
        'description',
        'price_cents',
        'is_active'
    ];

    private const array INGREDIENT_PIVOT_COLUMNS = [
        'store_id',
        'name',
        'description',
        'cost_per_unit_cents',
        'unit',
        'is_active',
        'is_mandatory',
        'is_allergen',
    ];

    public function __construct(
        private ItemRepositoryInterface $itemRepository
    ){}

    public function attachOptions(Item $item, array $options): Collection
    {
        return DB::transaction(function () use ($item, $options) {
            $syncData = [];
            foreach ($options as $dto) {
                $syncData[$dto->getPivotKey()] = $dto->getPivotData();
            }

            return $this->syncPivotData(
                model: $item,
                relation: 'options',
                data: $syncData,
                pivotColumns: self::OPTION_PIVOT_COLUMNS
            );
        });
    }

    public function attachIngredients(Item $item, array $ingredients): Collection
    {
        return DB::transaction(function () use ($item, $ingredients) {
            $syncData = [];
            foreach ($ingredients as $dto) {
                $syncData[$dto->getPivotKey()] = $dto->getPivotData();
            }

            return $this->syncPivotData(
                model: $item,
                relation: 'ingredients',
                data: $syncData,
                pivotColumns: self::INGREDIENT_PIVOT_COLUMNS
            );
        });
    }

    public function detachOption(Item $item, Option $option): bool
    {
        return DB::transaction(function () use ($item, $option) {
            $item->options()->detach($option->id);
            return true;
        });
    }

    public function detachIngredient(Item $item, Ingredient $ingredient): bool
    {
        return DB::transaction(function () use ($item, $ingredient) {
            $item->ingredients()->detach($ingredient->id);
            return true;
        });
    }

    public function detachIngredients(Item $item, array $ingredientIds): bool
    {
        return DB::transaction(function () use ($item, $ingredientIds) {
            $item->ingredients()->detach($ingredientIds);
            return true;
        });
    }
}
