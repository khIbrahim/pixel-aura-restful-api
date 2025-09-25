<?php

namespace App\Http\Requests\V1\Ingredient;

use Illuminate\Foundation\Http\FormRequest;

class DetachIngredientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ingredient_ids'   => ['required', 'array'],
            'ingredient_ids.*' => ['required', 'integer', 'exists:ingredients,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ingredient_ids.required'   => 'La liste des identifiants d\'ingrédients est requise.',
            'ingredient_ids.array'      => 'La liste des identifiants d\'ingrédients doit être un tableau.',
            'ingredient_ids.*.required' => 'Chaque identifiant d\'ingrédient est requis.',
            'ingredient_ids.*.integer'  => 'Chaque identifiant d\'ingrédient doit être un entier.',
            'ingredient_ids.*.exists'   => 'Un ou plusieurs ingrédients n\'existent pas.',
        ];
    }
}
