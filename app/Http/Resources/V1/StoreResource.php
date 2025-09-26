<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Store
 */
class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->sku,
            'phone'          => $this->phone,
            'email'          => $this->email,

            'address'        => [
                'street'     => $this->address,
                'city'       => $this->city,
                'postalCode' => $this->postal_code,
                'country'    => $this->country
            ],

            'settings'           => [
                'currency'       => $this->currency,
                'language'       => $this->language,
                'timezone'       => $this->timezone,
                'isTaxInclusive' => $this->tax_inclusive,
                'defaultVatRate' => $this->default_vat_rate !== null ? (float) $this->default_vat_rate : null,
                'advanced'       => $this->settings
            ],

            'ownerId' => $this->owner_id,
            'owner'   => $this->whenLoaded('owner', function () {
                return [
                    'name'       => $this->owner->name,
                    'first_name' => $this->owner->first_name ?? '',
                    'last_name'  => $this->owner->last_name ?? '',
                    'email'      => $this->owner->email
                ];
            }),

            'membersCount'   => $this->when(isset($this->members_count), (int) $this->members_count),

            'menuVersion'    => $this->menu_version,
            'isActive'       => $this->is_active,

            'createdAt'      => $this->created_at?->toISOString(),
            'updatedAt'      => $this->created_at?->toISOString(),
        ];
    }
}
