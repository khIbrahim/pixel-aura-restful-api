<?php

namespace App\Http\Requests\V1\ItemVariant;

use Illuminate\Foundation\Http\FormRequest;

class CreateItemVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price_cents' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la variante est requis',
            'name.string' => 'Le nom de la variante doit être une chaîne de caractères',
            'name.max' => 'Le nom de la variante ne doit pas dépasser 255 caractères',
            'description.string' => 'La description de la variante doit être une chaîne de caractères',
            'price_cents.required' => 'Le prix en centimes est requis',
            'price_cents.numeric' => 'Le prix en centimes doit être un nombre',
            'price_cents.min' => 'Le prix en centimes doit être au moins 0',
            'is_active.boolean' => 'Le champ is_active doit être vrai ou faux',
        ];
    }
}
