<?php

namespace App\Contracts\V1\Ingredient;

use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Models\V1\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IngredientRepositoryInterface
{

    public function findOrCreateIngredient(CreateIngredientDTO $data): Ingredient;

    public function findIngredient(int $id): ?Ingredient;

    public function createIngredient(CreateIngredientDTO $data): Ingredient;

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function update(Ingredient $ingredient, UpdateIngredientDTO $data): Ingredient;

    public function delete(Ingredient $ingredient): bool;

    public function findIngredientsByIds(array $ids): Collection;

}
