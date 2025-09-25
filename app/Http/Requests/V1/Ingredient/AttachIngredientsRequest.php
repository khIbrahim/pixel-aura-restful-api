<?php

namespace App\Http\Requests\V1\Ingredient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachIngredientsRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'ingredients'                       => ['required', 'array'],
            'ingredients.*.id'                  => ['required', 'integer', Rule::exists('ingredients', 'id')],
            'ingredients.*.name'                => ['sometimes', 'string', 'max:255'],
            'ingredients.*.description'         => ['sometimes', 'string'],
            'ingredients.*.unit'                => ['sometimes', 'string', 'max:100'],
            'ingredients.*.cost_per_unit_cents' => ['sometimes', 'integer', 'min:0'],
            'ingredients.*.is_mandatory'        => ['sometimes', 'boolean'],
            'ingredients.*.is_allergen'         => ['sometimes', 'boolean'],
            'ingredients.*.is_active'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'ingredients.*.id.exists'   => "L'ingrédient sélectionné n'existe pas",
            'ingredients.*.id.integer'  => "L'identifiant de l'ingrédient doit être un entier",
            'ingredients.*.id.required' => "L'identifiant de l'ingrédient est requis",
            'ingredients.required'     => "Au moins un ingrédient doit être fourni",
            'ingredients.array'        => "Les ingrédients doivent être fournis sous forme de tableau",
        ];
    }

}
