<?php

namespace App\Contracts\V1\Item;

use App\DTO\V1\Option\OptionPivotDTO;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\Option;
use Illuminate\Support\Collection;

interface ItemAttachmentServiceInterface
{
    /**
     * @param Item $item
     * @param OptionPivotDTO[] $options
     * @return Collection<Option>
     */
    public function attachOptions(Item $item, array $options): Collection;

    /**
     * @param Item $item
     * @param array $ingredients
     * @return Collection<Item>
     */
    public function attachIngredients(Item $item, array $ingredients): Collection;

    public function detachOption(Item $item, Option $option): bool;

    public function detachIngredient(Item $item, Ingredient $ingredient): bool;

    /**
     * @param Item $item
     * @param array $ingredientIds
     * @return bool
     */
    public function detachIngredients(Item $item, array $ingredientIds): bool;
}
