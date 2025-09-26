<?php

namespace App\Http\Requests\V1\ItemAttachment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachOptionListsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_lists'      => ['required', 'array', 'min:1'],
            'option_lists.*.id' => [
                'required',
                'integer',
                Rule::exists('option_lists', 'id')->where('store_id', $this->user()->store_id)
            ],
            'option_lists.*.is_required'    => ['sometimes', 'boolean'],
            'option_lists.*.min_selections' => ['sometimes', 'integer', 'min:0'],
            'option_lists.*.max_selections' => ['sometimes', 'integer', 'min:1'],
            'option_lists.*.display_order'  => ['sometimes', 'integer', 'min:0'],
            'option_lists.*.is_active'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'option_lists.required'             => 'Au moins une liste d\'options doit être fournie',
            'option_lists.array'                => 'Les listes d\'options doivent être fournies sous forme de tableau',
            'option_lists.min'                  => 'Au moins une liste d\'options doit être fournie',
            'option_lists.*.id.required'        => "L'identifiant de la liste d'options est requis",
            'option_lists.*.id.integer'         => "L'identifiant de la liste d'options doit être un entier",
            'option_lists.*.id.exists'          => "La liste d'options sélectionnée n'existe pas ou n'appartient pas à votre magasin",
            'option_lists.*.min_selections.min' => 'Le nombre minimum de sélections ne peut pas être négatif',
            'option_lists.*.max_selections.min' => 'Le nombre maximum de sélections doit être au moins 1',
            'option_lists.*.display_order.min'  => "L'ordre d'affichage ne peut pas être négatif",
        ];
    }

    public function attributes(): array
    {
        return [
            'option_lists.*.is_required'    => 'caractère obligatoire de la liste d\'options',
            'option_lists.*.min_selections' => 'nombre minimum de sélections',
            'option_lists.*.max_selections' => 'nombre maximum de sélections',
            'option_lists.*.display_order'  => 'ordre d\'affichage',
            'option_lists.*.is_active'      => 'statut actif de la liste d\'options',
        ];
    }

}
