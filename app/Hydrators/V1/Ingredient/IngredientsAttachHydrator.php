<?php

namespace App\Hydrators\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\Http\Requests\V1\Ingredient\AttachIngredientsRequest;
use App\Models\V1\Ingredient;
use Illuminate\Support\Facades\Cache;

readonly class IngredientsAttachHydrator
{

    public function __construct(
        private IngredientRepositoryInterface $ingredientRepository
    ){}

    public function fromRequest(AttachIngredientsRequest $request): array
    {
        $data           = $request->validated();
        $ingredientsIds = array_column($data['ingredients'], 'id');

        $existingIngredients = $this->ingredientRepository->findIngredientsByIds($ingredientsIds);

        $ingredients = [];
        foreach ($data['ingredients'] as $ingredientData){
            $id         = (int) $ingredientData['id'];
            /** @var Ingredient $ingredient */
            $ingredient = $existingIngredients->get($id);

            $ingredients[$id] = new IngredientPivotDTO(
                ingredientId: $ingredient->id,
                storeId: $ingredient->store_id,
                name: $ingredientData['name'] ?? $ingredient->name,
                description: $ingredientData['description'] ?? $ingredient->description,
                costPerUnitCents: $ingredientData['cost_per_unit_cents'] ?? $ingredient->cost_per_unit_cents,
                unit: $ingredientData['unit'] ?? $ingredient->unit,
                isActive: $ingredientData['is_active'] ?? $ingredient->is_active,
                isMandatory: $ingredientData['is_mandatory'] ?? $ingredient->is_mandatory,
                isAllergen: $ingredientData['is_allergen'] ?? $ingredient->is_allergen,
            );
        }

        return $ingredients;
    }

}
