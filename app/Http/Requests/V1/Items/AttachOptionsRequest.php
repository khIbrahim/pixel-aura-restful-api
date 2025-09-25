<?php

namespace App\Http\Requests\V1\Items;

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
            'options'               => ['required', 'array'],
            'options.*.id'          => ['required', 'integer', 'integer', Rule::exists('options', 'id')],
            'options.*.name'        => ['sometimes', 'string', 'max:255'],
            'options.*.description' => ['sometimes', 'string'],
            'options.*.price_cents' => ['sometimes', new MoneyRule()],
            'options.*.is_active'   => ['sometimes', 'boolean']
        ];
    }

}
