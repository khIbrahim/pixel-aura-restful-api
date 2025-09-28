<?php

namespace App\Http\Requests\V1\StoreMember;

use App\Rules\V1\CurrencyRule;
use App\Rules\V1\ImageRule;
use App\Rules\V1\ImageUrlRule;
use App\Rules\V1\MoneyRule;
use App\Rules\V1\SkuRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateItemRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'tax_id'      => ['nullable', 'integer', Rule::exists('taxes', 'id')],

            'name'        => ['required', 'string', 'max:255'],
            'sku'         => ['nullable', 'string', 'max:100', new SkuRule('items', $this->user()->store_id)],
            'barcode'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'currency'           => ['nullable', new CurrencyRule()],
            'base_price_cents'   => ['required', new MoneyRule()],
            'current_cost_cents' => ['nullable', new MoneyRule()],

            'is_active'        => ['boolean'],
            'track_inventory'  => ['boolean'],
            'stock'            => ['nullable', 'integer', 'min:0'],
            'loyalty_eligible' => ['boolean'],

            'age_restriction'  => ['nullable', 'integer', 'min:0'],
            'reorder_level'    => ['nullable', 'integer', 'min:0'],

            'weight_grams'     => ['nullable', 'integer', 'min:0'],

            'tags'                => ['nullable', 'array'],
            'tags.*'              => ['string', 'max:50'],
            'metadata'            => ['nullable', 'array'],

            'options'               => ['nullable', 'array'],
            'options.*.id'          => ['nullable', 'integer', Rule::exists('options', 'id')],
            'options.*.name'        => ['required_without:options.*.id', 'string', 'max:255'],
            'options.*.description' => ['required_without:options.*.id', 'description', 'string'],
            'options.*.price_cents' => ['required_with:options.*.name', 'integer', 'min:0'],

            'variants'               => ['nullable', 'array'],
            'variants.*.name'        => ['required_with:variants', 'string', 'max:255'],
            'variants.*.description' => ['required_with:variants', 'string'],
            'variants.*.sku'         => ['nullable', 'string', 'max:100', new SkuRule('item_variants')],
            'variants.*.price_cents' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.is_active'   => ['boolean'],

            'ingredients' => ['nullable', 'array'],

            'ingredients.*.id' => [
                'nullable',
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
            'ingredients.*.unit'                => ['nullable', 'string', 'max:50'],
            'ingredients.*.cost_per_unit_cents' => ['nullable', new MoneyRule()],

            'image'     => ['nullable', new ImageRule(), 'prohibits:image_url'],
            'image_url' => ['nullable', new ImageUrlRule(), 'prohibits:image'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists'        => "La catégorie sélectionnée n'existe pas.",
            'tax_id.exists'             => "La taxe sélectionnée n'existe pas.",
            'sku.unique'                => "Le SKU doit être unique au sein du magasin.",
            'currency.required'         => "La devise est obligatoire.",
            'base_price_cents.required' => "Le prix de base est obligatoire.",
            'base_price_cents.min'      => "Le prix de base doit être au moins de 0.",
            'current_cost_cents.min'    => "Le coût actuel doit être au moins de 0.",
            'stock.min'                 => "Le stock doit être au moins de 0.",
            'age_restriction.min'       => "La restriction d'âge doit être au moins de 0.",
            'reorder_level.min'         => "Le niveau de réapprovisionnement doit être au moins de 0.",
            'weight_grams.min'          => "Le poids en grammes doit être au moins de 0.",
            'tags.array'                => "Les tags doivent être un tableau.",
            'tags.*.string'             => "Chaque tag doit être une chaîne de caractères.",
            'tags.*.max'                => "Chaque tag ne doit pas dépasser 50 caractères.",
            'metadata.array'            => "Les métadonnées doivent être un tableau.",

            'options.array'                       => "Les options doivent être un tableau.",
            'options.*.id.exists'                 => "L'ID de l'option doit exister.",
            'options.*.name.required_without'     => "Le nom de l'option est obligatoire si l'ID n'est pas fourni.",
            'options.*.name.string'               => "Le nom de l'option doit être une chaîne de caractères.",
            'options.*.name.max'                  => "Le nom de l'option ne doit pas dépasser 255 caractères.",
            'options.*.price_cents.required_with' => "Le prix de l'option est obligatoire si le nom est fourni.",
            'options.*.price_cents.integer'       => "Le prix de l'option doit être un entier.",
            'options.*.price_cents.min'           => "Le prix de l'option doit être au moins de 0.",

            'variants.array'                       => "Les variantes doivent être un tableau.",
            'variants.*.name.required_with'        => "Le nom de la variante est obligatoire si des variantes sont fournies.",
            'variants.*.name.string'               => "Le nom de la variante doit être une chaîne de caractères.",
            'variants.*.name.max'                  => "Le nom de la variante ne doit pas dépasser 255 caractères.",
            'variants.*.sku.unique'                => "Le SKU de la variante doit être unique au sein du magasin.",
            'variants.*.price_cents.required_with' => "Le prix de la variante est obligatoire si des variantes sont fournies.",
            'variants.*.price_cents.integer'       => "Le prix de la variante doit être un entier.",
            'variants.*.price_cents.min'           => "Le prix de la variante doit être au moins de 0.",
            'variants.*.is_active.boolean'         => "Le champ is_active de la variante doit être un booléen.",

            'ingredients.array'                   => "Les ingrédients doivent être un tableau.",
            'ingredients.*.id.exists'             => "L'ID de l'ingrédient doit exister.",
            'ingredients.*.name.required_without' => "Le nom de l'ingrédient est obligatoire si l'ID n'est pas fourni.",
            'ingredients.*.name.string'           => "Le nom de l'ingrédient doit être une chaîne de caractères.",
            'ingredients.*.name.max'              => "Le nom de l'ingrédient ne doit pas dépasser 255 caractères.",
            'ingredients.*.is_mandatory.boolean'  => "Le champ is_mandatory de l'ingrédient doit être un booléen.",
            'ingredients.*.is_allergen.boolean'   => "Le champ is_allergen de l'ingrédient doit être un booléen.",
            'ingredients.*.name.unique'           => "Le nom de l'ingrédient doit être unique au sein du magasin.",

            'image.prohibits'     => "Vous ne pouvez pas fournir 'image' et 'image_url' en même temps.",
            'image_url.prohibits' => "Vous ne pouvez pas fournir 'image_url' et 'image' en même temps.",
            'type.required_with'  => "Le champ 'type' est obligatoire lorsque vous fournissez une image ou une image_url.",
            'type.in'             => "Le champ 'type' doit être soit 'thumbnail', 'banner' ou 'icon'.",
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency'  => $this->input('currency', config('app.currency')),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

}
