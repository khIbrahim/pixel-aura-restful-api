<?php

namespace App\Http\Requests\V1\OptionList;

use Illuminate\Foundation\Http\FormRequest;

class AttachToItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_lists' => ['required', 'array'],
            'option_lists.*.option_list_id' => ['required', 'integer', 'exists:option_lists,id'],
            'option_lists.*.is_required' => ['boolean'],
            'option_lists.*.min_selections' => ['nullable', 'integer', 'min:0'],
            'option_lists.*.max_selections' => ['nullable', 'integer', 'min:1'],
            'option_lists.*.display_order' => ['nullable', 'integer', 'min:0'],
            'option_lists.*.is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'option_lists.required' => 'La liste des option lists est requise.',
            'option_lists.array' => 'La liste des option lists doit être un tableau.',
            'option_lists.*.option_list_id.required' => 'L\'ID de l\'option list est requis.',
            'option_lists.*.option_list_id.exists' => 'L\'option list spécifiée n\'existe pas.',
            'option_lists.*.min_selections.min' => 'Le nombre minimum de sélections ne peut pas être négatif.',
            'option_lists.*.max_selections.min' => 'Le nombre maximum de sélections doit être au moins 1.',
        ];
    }
}
