<?php

namespace App\Contracts\V1\Ingredient;

use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Models\V1\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;

interface IngredientServiceInterface
{

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator;

    public function update(Ingredient $ingredient, UpdateIngredientDTO $data): Ingredient;

    public function create(CreateIngredientDTO $data): Ingredient;

    public function destroy(Ingredient $ingredient): bool;

}
