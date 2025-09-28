<?php

namespace App\Contracts\V1\Ingredient;

use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Exceptions\V1\Ingredient\IngredientCreationException;
use App\Exceptions\V1\Ingredient\IngredientDeletionException;
use App\Exceptions\V1\Ingredient\IngredientUpdateException;
use App\Models\V1\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;

interface IngredientServiceInterface
{

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * @throws IngredientUpdateException
     */
    public function update(Ingredient $ingredient, UpdateIngredientDTO $data): Ingredient;

    /**
     * @throws IngredientCreationException
     */
    public function create(CreateIngredientDTO $data): Ingredient;

    /**
     * @throws IngredientDeletionException
     */
    public function destroy(Ingredient $ingredient): bool;

}
