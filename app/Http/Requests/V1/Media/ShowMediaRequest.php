<?php

namespace App\Http\Requests\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

class ShowMediaRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'model' => ['required', ]
        ];
    }
}
