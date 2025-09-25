<?php

namespace App\Services\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Ingredient\IngredientServiceInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Models\V1\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class IngredientService implements IngredientServiceInterface
{

    public function __construct(
        private IngredientRepositoryInterface $ingredientRepository,
    ){}

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->ingredientRepository->list($filters, $perPage);
    }

    public function update(Ingredient $ingredient, UpdateIngredientDTO $data): Ingredient
    {
        return $this->ingredientRepository->update($ingredient, $data);
    }

    public function create(CreateIngredientDTO $data): Ingredient
    {
        return $this->ingredientRepository->createIngredient($data);
    }

    public function destroy(Ingredient $ingredient): bool
    {
        $ingredient->items()->detach();

        return $this->ingredientRepository->delete($ingredient);
    }
}
