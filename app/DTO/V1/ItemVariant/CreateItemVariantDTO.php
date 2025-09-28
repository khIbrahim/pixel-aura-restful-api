<?php

namespace App\DTO\V1\ItemVariant;

use App\DTO\V1\Abstract\BaseDTO;

final readonly class CreateItemVariantDTO extends BaseDTO
{
    public function __construct(
        public ?string $name        = null,
        public ?string $description = null,
        public ?int    $price_cents = null,
        public ?string $sku         = null,
        public bool    $is_active   = true,
        public int     $store_id,
        public ?int    $id          = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'sku'         => $this->sku,
            'is_active'   => $this->is_active,
            'store_id'    => $this->store_id
        ];
    }
}
