<?php

namespace App\Http\Requests\V1\Category;

use App\Rules\V1\ImageRule;
use App\Rules\V1\ImageUrlRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadCategoryImageRequest extends FormRequest
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
            'type'      => ['sometimes', 'string', 'in:thumbnail,banner,icon'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required_without'     => 'Une image ou une URL d\'image est requise.',
            'image.image'                => 'Le fichier doit être une image valide.',
            'image.max'                  => 'L\'image ne doit pas dépasser :max KB.',
            'image.dimensions'           => 'Les dimensions de l\'image ne respectent pas les contraintes.',
            'image_url.required_without' => 'Une URL d\'image ou un fichier est requis.',
            'image_url.url'              => 'L\'URL fournie n\'est pas valide.',
            'image_url.active_url'       => 'L\'URL fournie n\'est pas accessible.',
            'type.in'                    => 'Le type doit être: thumbnail, banner ou icon.',
        ];
    }

}
