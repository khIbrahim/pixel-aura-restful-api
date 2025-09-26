<?php

namespace App\Http\Requests\V1\ItemAttachment;

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
            'ingredients'      => ['required', 'array', 'min:1'],
            'ingredients.*.id' => [
                'required',
                'integer',
                Rule::exists('ingredients', 'id')->where('store_id', $this->attributes->get('store')->id)
            ],
            'ingredients.*.name'                => ['sometimes', 'string', 'max:255'],
            'ingredients.*.description'         => ['sometimes', 'string', 'max:1000'],
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
            'ingredients.required'                      => 'Au moins un ingrédient doit être fourni',
            'ingredients.array'                         => 'Les ingrédients doivent être fournis sous forme de tableau',
            'ingredients.min'                           => 'Au moins un ingrédient doit être fourni',
            'ingredients.*.id.required'                 => "L'identifiant de l'ingrédient est requis",
            'ingredients.*.id.integer'                  => "L'identifiant de l'ingrédient doit être un entier",
            'ingredients.*.id.exists'                   => "L'ingrédient sélectionné n'existe pas ou n'appartient pas à votre magasin",
            'ingredients.*.name.max'                    => 'Le nom ne peut pas dépasser 255 caractères',
            'ingredients.*.description.max'             => 'La description ne peut pas dépasser 1000 caractères',
            'ingredients.*.unit.max'                    => "L'unité ne peut pas dépasser 100 caractères",
            'ingredients.*.cost_per_unit_cents.integer' => 'Le coût par unité doit être un nombre entier',
            'ingredients.*.cost_per_unit_cents.min'     => 'Le coût par unité ne peut pas être négatif',
        ];
    }
}
