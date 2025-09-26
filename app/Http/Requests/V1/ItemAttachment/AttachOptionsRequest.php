<?php

namespace App\Http\Requests\V1\ItemAttachment;

use App\Rules\V1\MoneyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'options'      => ['required', 'array', 'min:1'],
            'options.*.id' => [
                'required',
                'integer',
                Rule::exists('options', 'id')->where('store_id', $this->user()->store_id)
            ],
            'options.*.name'        => ['sometimes', 'string', 'max:255'],
            'options.*.description' => ['sometimes', 'string'],
            'options.*.price_cents' => ['sometimes', new MoneyRule()],
            'options.*.is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.required'      => 'Au moins une option doit être fournie',
            'options.array'         => 'Les options doivent être fournies sous forme de tableau',
            'options.min'           => 'Au moins une option doit être fournie',
            'options.*.id.required' => "L'identifiant de l'option est requis",
            'options.*.id.integer'  => "L'identifiant de l'option doit être un entier",
            'options.*.id.exists'   => "L'option sélectionnée n'existe pas ou n'appartient pas à votre magasin",
            'options.*.name.max'    => 'Le nom ne peut pas dépasser 255 caractères',
        ];
    }
}
