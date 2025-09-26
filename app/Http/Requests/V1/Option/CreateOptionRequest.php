<?php

namespace App\Http\Requests\V1\Option;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOptionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'price_cents'    => ['required', 'integer', 'min:0'],
            'is_active'      => ['sometimes', 'boolean'],
            'option_list_id' => ['required', 'integer', Rule::exists('option_lists', 'id')->where('store_id', $this->attributes->get('store')->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'option_list_id.exists' => "La liste d'options sélectionnée est invalide ou n'existe pas.",
        ];
    }

}
