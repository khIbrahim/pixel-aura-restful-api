<?php

namespace App\Http\Resources\V1;

use App\Models\V1\ItemVariant;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ItemVariant
 */
class ItemVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price_cents' => $this->price_cents,
            'is_active' => $this->is_active,
        ];
    }
}
