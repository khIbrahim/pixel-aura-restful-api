<?php

namespace App\Http\Requests\V1\Item;

use App\Rules\V1\CurrencyRule;
use App\Rules\V1\ImageRule;
use App\Rules\V1\ImageUrlRule;
use App\Rules\V1\MoneyRule;
use App\Rules\V1\SkuRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'tax_id'      => ['sometimes', 'integer', Rule::exists('taxes', 'id')],

            'name'        => ['sometimes', 'string', 'max:255'],
            'sku'         => ['sometimes', 'string', 'max:100', new SkuRule('items', $this->user()->store_id)],
            'barcode'     => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],

            'currency'           => ['sometimes', new CurrencyRule()],
            'base_price_cents'   => ['sometimes', new MoneyRule()],
            'current_cost_cents' => ['sometimes', new MoneyRule()],

            'is_active'        => ['boolean'],
            'track_inventory'  => ['boolean'],
            'stock'            => ['sometimes', 'integer', 'min:0'],
            'loyalty_eligible' => ['boolean'],

            'age_restriction'  => ['sometimes', 'integer', 'min:0'],
            'reorder_level'    => ['sometimes', 'integer', 'min:0'],

            'weight_grams'     => ['sometimes', 'integer', 'min:0'],

            'tags'                => ['sometimes', 'array'],
            'tags.*'              => ['string', 'max:50'],
            'metadata'            => ['sometimes', 'array'],

            'options'               => ['sometimes', 'array'],
            'options.*.id'          => ['sometimes','integer','exists:options,id'],
            'options.*.name'        => ['required_without:options.*.id','string','max:255'],
            'options.*.price_cents' => ['required_with:options.*.name','integer','min:0'],

            'variants'               => ['sometimes', 'array'],
            'variants.*.name'        => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku'         => ['sometimes', 'string', 'max:100', new SkuRule('item_variants')],
            'variants.*.price_cents' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.is_active'   => ['boolean'],

            'ingredients' => ['sometimes', 'array'],

            'ingredients.*.id' => [
                'sometimes',
                'integer',
                Rule::exists('ingredients', 'id')
                    ->where(fn($q) => $q->where('store_id', $this->user()->store_id))
            ],

            'ingredients.*.name' => [
                'required_without:ingredients.*.id',
                'string',
                'max:255',
                Rule::when(
                    ! $this->input('ingredients.*.id'),
                    Rule::unique('ingredients', 'name')
                        ->where(fn($q) => $q->where('store_id', $this->user()->store_id))
                )
            ],

            'ingredients.*.is_mandatory'        => ['sometimes', 'boolean'],
            'ingredients.*.is_allergen'         => ['sometimes', 'boolean'],
            'ingredients.*.unit'                => ['sometimes', 'string', 'max:50'],
            'ingredients.*.cost_per_unit_cents' => ['sometimes', new MoneyRule()],

            'image'     => ['sometimes', new ImageRule(), 'prohibits:image_url'],
            'image_url' => ['sometimes', new ImageUrlRule(), 'prohibits:image'],
        ];
    }
}
