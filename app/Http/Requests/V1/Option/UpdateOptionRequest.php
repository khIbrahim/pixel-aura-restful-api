<?php

namespace App\Http\Requests\V1\Option;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOptionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'price_cents'    => ['sometimes', 'integer', 'min:0'],
            'is_active'      => ['sometimes', 'boolean'],
            'option_list_id' => ['sometimes', 'integer', Rule::exists('option_lists', 'id')->where('store_id', $this->attributes->get('store')->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'option_list_id.exists' => "La liste d'options sélectionnée est invalide ou n'existe pas.",
        ];
    }

}
