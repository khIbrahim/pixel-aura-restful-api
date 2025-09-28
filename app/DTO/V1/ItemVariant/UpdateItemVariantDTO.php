<?php

namespace App\DTO\V1\ItemVariant;

use App\DTO\V1\Abstract\BaseDTO;

final readonly class UpdateItemVariantDTO extends BaseDTO
{
    public function __construct(
        public int $id,
        public int $item_id,
        public int $store_id,
        public string $name,
        public ?string $description = null,
        public ?int $price_cents = null,
        public ?bool $is_active = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'is_active' => $this->is_active,
        ];
    }
}
