<?php

namespace App\Http\Requests\V1\Discount;

use App\Enum\V1\Discount\DiscountType;
use App\Rules\V1\DiscountConfigRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDiscountRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = DiscountType::tryFrom($this->input('type'));

        return [
            'name'                   => ['required', 'string', 'max:255'],
            'code'                   => ['nullable', 'string', 'max:100', Rule::unique('discounts', 'code')->where(fn($q) => $q->where('store_id', request()->attributes->get('store')->id))],
            'description'            => ['nullable', 'string'],
            'type'                   => ['required', 'string', Rule::enum(DiscountType::class)],
            'value'                  => $type ? $type->getValueRules() : ['nullable'],
            'config'                 => ['array', new DiscountConfigRule($this->input('type'))],
            'applicable_items'       => ['nullable', 'array'],
            'applicable_items.*'     => ['integer', Rule::exists('items', 'id')],
            'applicable_categories'  => ['nullable', 'array'],
            'applicable_categories.*'=> ['integer', Rule::exists('categories', 'id')],
            'excluded_items'         => ['nullable', 'array'],
            'excluded_items.*'       => ['integer', Rule::exists('items', 'id')],
            'valid_from'             => ['nullable', 'date'],
            'valid_until'            => ['nullable', 'date', 'after:valid_from'],
            'max_uses'               => ['nullable', 'integer', 'min:1'],
            'max_uses_per_customer'  => ['nullable', 'integer', 'min:1'],
            'max_discount_cents'     => ['nullable', 'integer', 'min:0'],
            'is_combinable'          => ['required', 'boolean'],
            'combinable_with'        => ['nullable', 'array'],
            'combinable_with.*'      => ['integer', Rule::exists('discounts', 'id')],
            'metadata'               => ['nullable', 'array'],
            'min_order_amount_cents' => ['nullable', 'integer', 'min:0'],
            'min_items_quantity'     => ['nullable', 'integer', 'min:0'],
            'min_customer_orders'    => ['nullable', 'integer', 'min:0'],
            'max_order_amount_cents' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('value') && $this->has('type')) {
            $type = DiscountType::tryFrom($this->input('type'));
            $displayValue = $this->input('value'); // 20.5 ou 500.0

            if ($type && $displayValue !== null) {
                $storageValue = $this->toStorage($type, (float) $displayValue);

                $this->merge(['value' => $storageValue]);
            }
        }
    }

    private function toStorage(DiscountType $type, float $displayValue): ?int
    {
        return match($type) {
            DiscountType::Percentage, DiscountType::FirstOrder, DiscountType::HappyHour, DiscountType::FixedAmount, DiscountType::ReduceDelivery
                => (int) ($displayValue * 100),

            DiscountType::Quantity => (int) $displayValue,
            default                => null,
        };
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Le nom de la réduction est requis.',
            'name.max'                  => 'Le nom ne doit pas dépasser :max caractères.',
            'code.unique'               => 'Ce code promo existe déjà pour ce magasin.',
            'code.max'                  => 'Le code ne doit pas dépasser :max caractères.',
            'type.required'             => 'Le type de réduction est requis.',
            'type.enum'                 => 'Le type de réduction sélectionné est invalide.',
            'value.required'            => 'La valeur de la réduction est requise.',
            'value.numeric'             => 'La valeur doit être un nombre.',
            'value.min'                 => 'La valeur doit être au minimum :min.',
            'value.between'             => 'La valeur doit être entre :min et :max.',
            'config.required'           => 'La configuration est requise pour ce type.',
            'valid_from.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'valid_until.after'         => 'La date de fin doit être après la date de début.',
            'max_order_amount_cents.gt' => 'Le montant maximum doit être supérieur au montant minimum.',
        ];
    }

}
