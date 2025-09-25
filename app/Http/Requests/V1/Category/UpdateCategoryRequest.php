<?php

namespace App\Http\Requests\V1\Category;

use App\DTO\V1\Category\UpdateCategoryDTO;
use App\Models\V1\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'nullable'],
            'tags'        => ['sometimes', 'array'],
            'tags.*'      => ['string', 'max:50'],
            'position'    => ['sometimes', 'integer', 'min:1'],
            'parent_id'   => ['sometimes', 'integer', Rule::exists('categories', 'id')->where(
                fn($query) => $query->where('store_id', $this->user()->store_id)
            )],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.exists'     => "La catégorie parente spécifiée n'existe pas dans votre store.",
            'tags.array'           => "Le champ tags doit être un tableau.",
            'tags.*.string'        => "Chaque tag doit être une chaîne de caractères.",
            'tags.*.max'           => "Chaque tag ne doit pas dépasser 50 caractères.",
            'position.min'         => "La position doit être au moins 1.",
            'position.integer'     => "La position doit être un entier.",
            'is_active.boolean'    => "Le champ is_active doit être un booléen.",
            'name.max'             => "Le nom ne doit pas dépasser 255 caractères.",
            'description.string'   => "La description doit être une chaîne de caractères.",
        ];
    }

    public function toDTO(): UpdateCategoryDTO
    {
        return UpdateCategoryDTO::fromArray($this->validated());
    }
}
