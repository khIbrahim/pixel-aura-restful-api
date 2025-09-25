<?php

namespace App\Http\Requests\V1\OptionList;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOptionListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string', 'max:1000'],
            'min_selections' => ['sometimes', 'integer', 'min:0'],
            'max_selections' => ['sometimes', 'nullable', 'integer', 'min:1', 'gte:min_selections'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'                   => 'Le nom ne peut pas dépasser 255 caractères.',
            'description.max'            => 'La description ne peut pas dépasser 1000 caractères.',
            'min_selections.integer'     => 'Le nombre minimum de sélections doit être un entier.',
            'min_selections.min'         => 'Le nombre minimum de sélections ne peut pas être négatif.',
            'max_selections.integer'     => 'Le nombre maximum de sélections doit être un entier.',
            'max_selections.min'         => 'Le nombre maximum de sélections doit être au moins 1.',
            'max_selections.gte'         => 'Le nombre maximum de sélections doit être supérieur ou égal au minimum.',
        ];
    }
}
