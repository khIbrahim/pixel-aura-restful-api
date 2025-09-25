<?php

namespace App\Http\Requests\V1\Ingredient;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIngredientRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'is_allergen'  => ['sometimes', 'boolean'],
            'is_mandatory' => ['sometimes', 'boolean'],
            'is_active'    => ['sometimes', 'boolean'],
            'unit'         => ['sometimes', 'string', 'max:100', 'nullable'],
            'cost_per_unit_cents' => ['sometimes', 'integer', 'min:0']
        ];
    }
}
