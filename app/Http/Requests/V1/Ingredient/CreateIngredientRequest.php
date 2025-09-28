<?php

namespace App\Http\Requests\V1\Ingredient;

use Illuminate\Foundation\Http\FormRequest;

class CreateIngredientRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'is_allergen'         => ['boolean'],
            'is_mandatory'        => ['boolean'],
            'cost_per_unit_cents' => ['required', 'integer', 'min:0'],
            'unit'                => ['nullable', 'string', 'max:50'],
            'is_active'           => ['boolean'],
        ];
    }
}
