<?php

namespace App\Http\Requests\V1\StoreMember;

use App\Rules\V1\CurrencyRule;
use App\Rules\V1\LanguageRule;
use App\Rules\V1\TimezoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store = $this->route('store');

        return [
            'name'        => ['sometimes', 'string', 'min:5', 'max:120', Rule::unique('stores', 'name')->ignore($store)],
            'sku'         => ['sometimes', 'string', 'min:2', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('stores', 'sku')->ignore($store)],
            'phone'       => ['sometimes', 'nullable', 'phone:ZZ'],
            'email'       => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('stores', 'email')->ignore($store)],

            'address'     => ['sometimes', 'nullable', 'string', 'max:500'],
            'city'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'country'     => ['sometimes', 'nullable', 'string', 'max:50'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],

            'currency'    => ['sometimes', 'nullable', new CurrencyRule()],
            'language'    => ['sometimes', 'nullable', new LanguageRule()],
            'timezone'    => ['sometimes', 'nullable', new TimezoneRule()],

            'receipt_settings'             => ['sometimes', 'array'],
            'receipt_settings.header_text' => ['nullable', 'string', 'max:500'],
            'receipt_settings.footer_text' => ['nullable', 'string', 'max:500'],
            'receipt_settings.print_logo'  => ['boolean'],
            'settings' => ['sometimes', 'array'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Le nom doit contenir au moins 5 caractères',
            'name.unique' => 'Le nom du magasin est déjà utilisé',
            'sku.unique' => 'Ce nom court est déjà utilisé',
            'sku.regex' => 'Le nom court ne peut contenir que des lettres minuscules, chiffres et tirets',
            'email.unique' => 'Cet email est déjà utilisé',
        ];
    }
}
