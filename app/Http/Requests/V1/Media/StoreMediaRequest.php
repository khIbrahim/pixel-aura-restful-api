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
            'image'      => ['nullable', new ImageRule(), 'prohibits:image_url'],
            'image_url'  => ['nullable', new ImageUrlRule(), 'prohibits:image'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.prohibits'     => 'Vous pouvez fournir uniquement une image ou une URL d\'image',
            'image_url.prohibits' => 'Vous pouvez fournir uniquement une image ou une URL d\'image',
        ];
    }
}
