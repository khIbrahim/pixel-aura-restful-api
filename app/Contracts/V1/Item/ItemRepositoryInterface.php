<?php

namespace App\Contracts\V1\Item;

use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\CreateVariantDTO;
use App\DTO\V1\Option\CreateOptionDTO;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Option;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ItemRepositoryInterface
{

    public function createItem(CreateItemDTO $data): Item;

    public function createVariant(Item $item, CreateVariantDTO $data): ItemVariant;

    public function attachIngredient(Item $item, Ingredient $ingredient, CreateIngredientDTO $data): void;

    public function attachOption(Item $item, Option $option, CreateOptionDTO $data): void;

    public function bulkCreateVariants(Item $item, array $variants): void;

    public function getItemsByCategory(int $categoryId): Collection;

    public function findItem(int $id, bool $withRelations = true): ?Item;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * @param Item $item
     * @param array $options
     * @return Collection<Option>
     */
    public function attachOptions(Item $item, array $options): Collection;


}
