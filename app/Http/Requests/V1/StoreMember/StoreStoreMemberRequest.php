<?php

namespace App\Http\Requests\V1\StoreMember;

use App\Enum\StoreMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreStoreMemberRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role    = $this->enum('role', StoreMemberRole::class);

        return [
            'name'      => ['bail', 'required', 'string', 'max:120'],
            'role'      => ['bail', 'required', new Enum(StoreMemberRole::class)],
            'pin'       => [
                'string',
                'regex:/^\\d{4,8}$/',
                Rule::requiredIf(fn() => in_array($role, [StoreMemberRole::Owner, StoreMemberRole::Manager], true)),
            ],
            'is_active'   => ['sometimes', 'boolean'],
            'meta'        => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        $roles = implode(', ', array_map(fn($r) => $r->value, StoreMemberRole::cases()));
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.unique'   => 'Ce nom est déjà utilisé dans ce magasin.',
            'role.required' => "Le rôle est obligatoire ($roles).",
            'pin.regex'     => 'Le PIN doit contenir 4 à 8 chiffres (zéros autorisés).',
            'pin.required'  => 'Le PIN est obligatoire pour les rôles OWNER et Manager'
        ];
    }

}
