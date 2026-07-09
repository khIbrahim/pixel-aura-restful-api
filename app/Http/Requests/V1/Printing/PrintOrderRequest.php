<?php

namespace App\Http\Requests\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'types'   => ['required', 'array', 'min:1'],
            'types.*' => ['required', 'string', Rule::in(['customer', 'receipt', 'kitchen'])],
        ];
    }
}
