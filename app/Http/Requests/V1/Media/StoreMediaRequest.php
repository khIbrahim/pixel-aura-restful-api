<?php

namespace App\Http\Requests\V1\Media;

use App\Rules\V1\ImageRule;
use App\Rules\V1\ImageUrlRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image'     => ['required_without:image_url', new ImageRule()],
            'image_url' => ['required_without:image', new ImageUrlRule()],
        ];
    }
}
