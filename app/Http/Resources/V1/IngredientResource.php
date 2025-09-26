<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Ingredient;
use Illuminate\Http\Request;

/**
 * @mixin Ingredient
 */
class IngredientResource extends AbstractPivotResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->getPivotValue('id'),
            'store_id'            => $this->getPivotValue('store_id'),
            'name'                => $this->getPivotValue('name'),
            'description'         => $this->getPivotValue('description'),
            'is_allergen'         => $this->getPivotValue('is_allergen'),
            'unit'                => $this->getPivotValue('unit'),
            'cost_per_unit_cents' => $this->getPivotValue('cost_per_unit_cents'),
            'created_at'          => $this->getPivotValue('created_at'),
            'updated_at'          => $this->getPivotValue('updated_at'),
        ];
    }

}
