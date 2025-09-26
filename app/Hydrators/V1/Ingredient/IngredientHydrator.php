<?php

namespace App\Hydrators\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\Http\Requests\V1\ItemAttachment\AttachIngredientsRequest;
use App\Hydrators\V1\BaseHydrator;
use App\Models\V1\Ingredient;

class IngredientHydrator extends BaseHydrator
{

    public function __construct(
        private readonly IngredientRepositoryInterface $ingredientRepository,
    ){}

    public function fromAttachRequest(AttachIngredientsRequest $request, bool $asArray = true): array
    {
        $data           = $request->validated();
        $ingredientsIds = array_column($data['ingredients'] ?? [], 'id');
        if(empty($ingredientsIds)) {
            return [];
        }

        $existingIngredients = $this->ingredientRepository->findIngredientsByIds($ingredientsIds);

        $ingredients = [];
        foreach ($data['ingredients'] as $ingredientData) {
            $id = (int)$ingredientData['id'];
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

        return $asArray ? array_map(fn($i) => $i->toArray(), $ingredients) : $ingredients;
    }
}
