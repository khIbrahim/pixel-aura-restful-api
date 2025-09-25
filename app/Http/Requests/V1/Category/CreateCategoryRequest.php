<?php

namespace App\Http\Requests\V1\Category;

use App\DTO\V1\Category\CreateCategoryDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['string', 'max:50'],
            'position'    => ['nullable', 'integer', 'min:1'],
            'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')->where(function ($query) {
                $query->where('store_id', $this->attributes->get('store')->id);
            })],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis',
            'name.string'   => 'Le nom doit être une chaîne de caractères',
            'name.max'      => 'Le nom ne doit pas dépasser 255 caractères',

            'description.string' => 'La description doit être une chaîne de caractères',

            'tags.array'    => 'Les tags doivent être un tableau',
            'tags.*.string' => 'Chaque tag doit être une chaîne de caractères',
            'tags.*.max'    => 'Chaque tag ne doit pas dépasser 50 caractères',

            'position.integer' => 'La position doit être un entier',
            'position.min'     => 'La position doit être au moins 1',

            'parent_id.integer' => "L'identifiant du parent doit être un entier",
            'parent_id.exists'  => "La catégorie parente spécifiée n'existe pas dans ce store",

            'is_active.boolean' => "Le champ d'état actif doit être vrai ou faux",
        ];
    }

    public function toDTO(): CreateCategoryDTO
    {
        return CreateCategoryDTO::fromRequest(array_merge(
            $this->validated(),
            ['store_id' => $this->user()->store_id]
        ));
    }
}
