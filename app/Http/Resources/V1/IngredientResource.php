<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ingredient
 */
class IngredientResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->pivot->id ?? $this->id,
            'store_id'            => $this->pivot->store_id ?? $this->store_id,
            'name'                => $this->pivot->name ?? $this->name,
            'description'         => $this->pivot->description ?? $this->description,
            'is_allergen'         => $this->pivot->is_allergen ?? $this->is_allergen,
            'unit'                => $this->pivot->unit ?? $this->unit,
            'cost_per_unit_cents' => $this->pivot->cost_per_unit_cents ?? $this->cost_per_unit_cents,
            'created_at'          => $this->pivot->created_at ?? $this->created_at,
            'updated_at'          => $this->pivot->updated_at ?? $this->updated_at,
        ];
    }
}
