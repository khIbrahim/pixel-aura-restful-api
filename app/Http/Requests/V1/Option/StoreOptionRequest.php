<?php

namespace App\Http\Requests\V1\Option;

use Illuminate\Foundation\Http\FormRequest;

class StoreOptionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
