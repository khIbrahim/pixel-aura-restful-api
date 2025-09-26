<?php

namespace App\Http\Requests\V1\StoreMember;

use App\Rules\V1\CurrencyRule;
use App\Rules\V1\LanguageRule;
use App\Rules\V1\TimezoneRule;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Locale;

class CreateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'min:5', 'max:120', Rule::unique('stores', 'name')],
            'sku'              => ['nullable', 'string', 'min:2', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('stores', 'sku')],
            'phone'            => ['nullable', 'phone:ZZ'],
            'email'            => ['nullable', 'email', 'max:255', Rule::unique('stores')],

            'address'          => ['nullable', 'string', 'max:500'],
            'city'             => ['nullable', 'string', 'max:100'],
            'country'          => ['nullable', 'string', 'max:50'],
            'postal_code'      => ['nullable', 'string', 'max:20'],

            'currency'         => ['nullable', new CurrencyRule()],
            'language'         => ['nullable', new LanguageRule()],
            'timezone'         => ['nullable', new TimezoneRule()],


            'receipt_settings'             => ['nullable', 'array'],
            'receipt_settings.header_text' => ['nullable', 'string', 'max:500'],
            'receipt_settings.footer_text' => ['nullable', 'string', 'max:500'],
            'receipt_settings.print_logo'  => ['boolean'],

            'settings'  => ['nullable', 'array'],

            'owner_id'       => ['sometimes', 'array'],
            'owner.name'     => ['required_with:owner', 'string', 'min:2', 'max:255'],
            'owner.email'    => ['required_with:owner', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'owner.password' => ['required_with:owner', 'string', Password::default(), 'confirmed']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Le nom du magasin est obligatoire',
            'name.min'           => 'Le nom doit contenir au moins 2 caractères',
            'sku.unique'         => 'Ce nom court est déjà utilisé',
            'sku.regex'          => 'Le nom court ne peut contenir que des lettres minuscules, chiffres et tirets',
            'email.unique'       => 'Cet email est déjà utilisé',
            'country.size'       => 'Le code pays doit être au format ISO (ex: FR)',
            'currency.size'      => 'La devise doit être au format ISO (ex: EUR)',
            'owner.email.unique' => 'Cet email propriétaire est déjà utilisé',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        $preferred = $this->getPreferredLanguage();
        $locale    = str_replace('-', '_', $preferred ?: '');
        $language  = $this->input('language') ?: (Locale::getPrimaryLanguage($locale) ?: 'fr');
        $country   = $this->input('country') ?: strtoupper(Locale::getRegion($locale) ?: '');
        $currency  = $this->currency ?? 'DZD';

        $timezone = $this->input('timezone');
        if (! $timezone && $country) {
            $tzs = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country);
            $timezone = $tzs[0] ?? null;
        }

        $timezone = $timezone ?: 'Africa/Algiers';

        $data['country']  = $country;
        $data['currency'] = $currency;
        $data['timezone'] = $timezone;
        $data['language'] = $language;

        $this->merge($data);
    }

}
