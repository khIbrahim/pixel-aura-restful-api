<?php

namespace App\Http\Requests\V1\Order;

use App\Enum\V1\Order\OrderChannel;
use App\Enum\V1\Order\OrderServiceType;
use App\Rules\V1\CurrencyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class CreateOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel'              => ['required', 'string', Rule::enum(OrderChannel::class)],
            'service_type'         => ['required', 'string', Rule::enum(OrderServiceType::class)],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'currency'             => ['nullable', new CurrencyRule()],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.id'         => ['required', 'integer', Rule::exists('items', 'id')->where(fn($q) => $q->where('store_id', $this->attributes->get('store')->id))],
            'items.*.variant_id' => ['nullable', 'integer', Rule::exists('item_variants', 'id')->where(fn($q) => $q->where('store_id', $this->attributes->get('store')->id))],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],

            'items.*.selected_options'             => ['nullable', 'array'],
            'items.*.selected_options.*.id'        => ['required', 'integer', Rule::exists('options', 'id')->where(fn($q) => $q->where('store_id', $this->attributes->get('store')->id))],
            'items.*.selected_options.*.quantity'  => ['required', 'integer', 'min:1'],

            'items.*.ingredient_modifications'            => ['nullable', 'array'],
            'items.*.ingredient_modifications.*.id'       => ['required', 'integer', Rule::exists('ingredients', 'id')->where(fn($q) => $q->where('store_id', $this->attributes->get('store')->id))],
            'items.*.ingredient_modifications.*.action'   => ['required', 'string', 'in:add,remove'],
            'items.*.ingredient_modifications.*.quantity' => ['required_if:items.*.ingredient_modifications.*.action,add', 'integer', 'min:1', 'max:10'],

            'items.*.special_instructions' => ['nullable', 'string', 'max:200'],

            'delivery'               => ['nullable', 'array'],
            'delivery.address'       => ['required_with:delivery', 'string', 'max:500'],
            'delivery.contact_name'  => ['required_with:delivery', 'string', 'max:100'],
            'delivery.contact_phone' => ['required_with:delivery', new Phone()],
            'delivery.notes'         => ['nullable', 'string', 'max:500'],
            'delivery.fee_cents'     => ['nullable', 'integer', 'min:0'],

            'dine_in'                  => ['nullable', 'array'],
            'dine_in.table_number'     => ['required_with:dine_in', 'string', 'max:10', /** TODO : Rule::exists('number', 'tables')->where('store_id', $this->>attributes->get('store')->id) */],
            'dine_in.number_of_guests' => ['nullable', 'integer', 'min:1', 'max:100'],
            'dine_in.server_id'        => ['nullable', 'integer', Rule::exists('store_members', 'id')->where(fn($q) => $q->where('store_id', $this->attributes->get('store')->id))],

            'pickup'               => ['nullable', 'array'],
            'pickup.contact_name'  => ['required_with:pickup', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'channel.required'                                        => "Le canal de la commande est requis.",
            'channel.string'                                          => "Le canal de la commande doit être une chaîne de caractères.",
            'channel.in'                                              => "Le canal de la commande spécifié est invalide.",
            'store_id.exists'                                         => "Le store spécifiée n'existe pas.",
            'items.required'                                          => "La commande doit au moins contenir un item.",
            'items.*.item_id.exists'                                  => "Un des items spécifiés n'existe pas.",
            'items.*.quantity.min'                                    => "La quantité doit au moins être de 1.",
            'items.*.quantity.max'                                    => "La quantité ne doit pas dépasser 100.",
            'items.*.selected_options.*.exists'                       => "Une des options spécifiées n'existe pas",
            'items.*.ingredient_modifications.*.ingredient_id.exists' => "Un de ces ingrédients spécifiés n'existe pas.",
            'items.*.ingredient_modifications.*.action.in'            => "L'action sur l'ingrédient doit être 'add' ou 'remove'.",
            'delivery.*.address.required_with'                        => "L'adresse de livraison est requise pour une commande de type livraison.",
            'delivery.*.contact_name.required_with'                   => "Le nom du contact est requis pour une commande de type livraison.",
            'delivery.*.contact_phone.required_with'                  => "Le téléphone du contact est requis pour une commande de type livraison.",
            'delivery.*.contact_phone.phone'                          => "Le téléphone du contact n'est pas valide.",
            'delivery.*.fee_cents.integer'                            => "Les frais de livraison doivent être un montant en cents.",
            'delivery.*fee_cents.min'                                 => "Les frais de livraison doivent être un montant positif.",
            'dine_in.*.table_number.required_with'                    => "Le numéro de table est requis pour une commande de type sur place.",
            'dine_in.*.table_number.exists'                           => "Le numéro de table spécifié n'existe pas.",
            'dine_in.*.number_of_guests.min'                          => "Le nombre de convives doit au moins être de 1.",
            'dine_in.*.number_of_guests.max'                          => "Le nombre de convives ne doit pas dépasser 100.",
            'dine_in.*.server_id.exists'                              => "Le serveur spécifié n'existe pas.",
            'pickup.*.contact_name.required_with'                     => "Le nom du contact est requis pour une commande de type à emporter.",
        ];
    }

}
