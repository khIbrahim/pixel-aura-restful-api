<?php

namespace App\Services\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Ingredient\IngredientServiceInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Events\V1\Ingredient\IngredientCreated;
use App\Events\V1\Ingredient\IngredientDeleted;
use App\Events\V1\Ingredient\IngredientUpdated;
use App\Exceptions\V1\Ingredient\IngredientCreationException;
use App\Exceptions\V1\Ingredient\IngredientDeletionException;
use App\Exceptions\V1\Ingredient\IngredientUpdateException;
use App\Models\V1\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
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

            /* @var Ingredient $ingredient */
            $ingredient = $this->ingredientRepository->update($ingredient, collect($data->toArray())->filter(fn($val) => $val !== null)->toArray());

            Log::info("Ingrédient mis à jour avec succès", [
                'ingredient_id' => $ingredient->id,
                'store_id'      => $ingredient->store_id,
                'name'          => $ingredient->name
            ]);

            broadcast(new IngredientUpdated(
                ingredient: $ingredient,
                sender_device_id: request()->attributes->get('device')?->id ?? null,
                sender_device_type: request()->attributes->get('device')?->type?->value ?? null,
                correlation_id: request()->headers->get('X-Correlation-ID')
            ))->toOthers();

            return $ingredient;
        } catch (Throwable $e){
            Log::error("Erreur lors de la mise à jour de l'ingrédient: " . $e->getMessage(), [
                'ingredient_id' => $ingredient->id,
                'store_id'      => $ingredient->store_id,
                'name'          => $ingredient->name,
                'exception'     => $e
            ]);

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

            /* @var Ingredient $ingredient */
            $ingredient = $this->ingredientRepository->create($data->toArray());

            Log::info("Ingrédient créé avec succès", [
                'ingredient_id' => $ingredient->id,
                'store_id'      => $ingredient->store_id,
                'name'          => $ingredient->name
            ]);

            broadcast(new IngredientCreated(
                ingredient: $ingredient,
                sender_device_id: request()->attributes->get('device')?->id ?? null,
                sender_device_type: request()->attributes->get('device')?->type?->value ?? null,
                correlation_id: request()->headers->get('X-Correlation-ID')
            ))->toOthers();

            return $ingredient;
        } catch (Throwable $e){
            Log::error("Erreur lors de la création de l'ingrédient: " . $e->getMessage(), [
                'store_id'  => $data->store_id,
                'name'      => $data->name,
                'exception' => $e
            ]);

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

            $deleted = $this->ingredientRepository->delete($ingredient);

            if($deleted){
                Log::info("Ingrédient supprimé avec succès", [
                    'ingredient_id' => $ingredient->id,
                    'store_id'      => $ingredient->store_id,
                    'name'          => $ingredient->name
                ]);
            } else {
                Log::warning("Échec de la suppression de l'ingrédient", [
                    'ingredient_id' => $ingredient->id,
                    'store_id'      => $ingredient->store_id,
                    'name'          => $ingredient->name
                ]);
            }

            broadcast(new IngredientDeleted(
                ingredient_id: $ingredient->id,
                store_id: $ingredient->store_id,
                sender_device_id: request()->attributes->get('device')?->id ?? null,
                sender_device_type: request()->attributes->get('device')?->type?->value ?? null,
                correlation_id: request()->headers->get('X-Correlation-ID')
            ));
            return $deleted;
        } catch (Throwable $e){
            Log::error("Erreur lors de la suppression de l'ingrédient: " . $e->getMessage(), [
                'ingredient_id' => $ingredient->id,
                'store_id'      => $ingredient->store_id,
                'name'          => $ingredient->name,
                'exception'     => $e
            ]);

            if($e instanceof IngredientDeletionException){
                throw $e;
            }

            throw IngredientDeletionException::default($e->getMessage());
        }
    }
}
