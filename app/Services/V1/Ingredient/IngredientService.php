<?php

namespace App\Services\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Ingredient\IngredientServiceInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Exceptions\V1\Ingredient\IngredientCreationException;
use App\Exceptions\V1\Ingredient\IngredientDeletionException;
use App\Exceptions\V1\Ingredient\IngredientUpdateException;
use App\Models\V1\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

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
        try {
            $name = $data->name ?? null;
            if($name && $name !== $ingredient->name && $this->ingredientRepository->findBy('name', $name)){
                throw IngredientUpdateException::nameAlreadyExists($name);
            }

            if(isset($data->is_allergen) && $data->is_allergen !== $ingredient->is_allergen && $ingredient->items()->where('is_active', true)->exists()){
                throw IngredientUpdateException::cannotChangeAllergenStatus();
            }

            /* @var Ingredient */
            return $this->ingredientRepository->update($ingredient, collect($data->toArray())->filter(fn($val) => $val !== null)->toArray());
        } catch (Throwable $e){
            if($e instanceof IngredientUpdateException){
                throw $e;
            }

            throw IngredientUpdateException::default($e->getMessage());
        }
    }

    public function create(CreateIngredientDTO $data): Ingredient
    {
        try {
            if($this->ingredientRepository->findBy('name', $data->name)){
                throw IngredientCreationException::nameAlreadyExists($data->name);
            }

            /* @var Ingredient */
            return $this->ingredientRepository->create($data->toArray());
        } catch (Throwable $e){
            if($e instanceof IngredientCreationException){
                throw $e;
            }

            throw IngredientCreationException::default($e->getMessage());
        }
    }

    public function destroy(Ingredient $ingredient): bool
    {
        try {
            if ($ingredient->hasActiveItems()){
                throw IngredientDeletionException::usedInActiveItems();
            }

            if($ingredient->is_mandatory){
                throw IngredientDeletionException::isMandatoryAllergen();
            }

            $ingredient->items()->detach();

            return $this->ingredientRepository->delete($ingredient);
        } catch (Throwable $e){
            if($e instanceof IngredientDeletionException){
                throw $e;
            }

            throw IngredientDeletionException::default($e->getMessage());
        }
    }
}
