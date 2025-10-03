<?php

namespace App\Http\Requests\V1\StoreMember;

use App\Constants\V1\StoreTokenAbilities;
use App\Enum\V1\StoreMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateStoreMemberRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId  = $this->route('store_member')->store_id;
        $memberId = $this->route('store_member')->id;
        $role     = $this->enum('role', StoreMemberRole::class);
        $all      = StoreTokenAbilities::all();

        return [
            'name'          => ['sometimes', 'string', 'max:120',
                Rule::unique('store_members', 'name')
                    ->where(fn($q) => $q->where('store_id', $storeId))
                    ->ignore($memberId)
            ],
            'role'          => ['sometimes', new Enum(StoreMemberRole::class)],
            'pin'           => [
                'sometimes',
                'string',
                'regex:/^\\d{4,8}$/',
                Rule::requiredIf(fn() => in_array($role, [StoreMemberRole::Owner, StoreMemberRole::Manager], true)),
            ],
            'is_active'     => ['sometimes', 'boolean'],
            'meta'          => ['sometimes', 'array'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in($all)]
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
            'pin.required'  => 'Le PIN est obligatoire pour les rôles OWNER et Manager',
            'permissions.*.in' => 'Permission inconnue.',
        ];
    }
}
