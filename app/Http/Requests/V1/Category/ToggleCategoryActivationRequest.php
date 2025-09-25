<?php

namespace App\Http\Requests\V1\Category;

use App\Models\V1\Category;
use Illuminate\Foundation\Http\FormRequest;

class ToggleCategoryActivationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function active(Category $category): bool
    {
        if ($this->has('is_active')){
            return $this->boolean('is_active');
        }

        return ! $category->is_active;
    }

}
