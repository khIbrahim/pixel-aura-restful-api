<?php

namespace App\Contracts\V1\Item;

use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\DTO\V1\Option\OptionPivotDTO;
use App\DTO\V1\OptionList\OptionListPivotDTO;
use App\Models\V1\Item;

interface ItemAttachmentServiceInterface
{
    public function attachIngredient(Item $item, array|IngredientPivotDTO $data): void;

    public function detachIngredient(Item $item, int $ingredientId): void;

    public function attachOption(Item $item, array|OptionPivotDTO $data): void;

    public function detachOption(Item $item, int $optionId): void;

    public function attachOptionList(Item $item, array|OptionListPivotDTO $data): void;

    public function detachOptionList(Item $item, int $optionListId): void;

    public function detachAllIngredients(Item $item): void;

    public function detachAllOptions(Item $item): void;

    public function detachAllOptionLists(Item $item): void;

}
