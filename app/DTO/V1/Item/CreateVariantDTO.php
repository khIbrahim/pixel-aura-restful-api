<?php

namespace App\DTO\V1\Item;

use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateVariantDTO implements Arrayable
{
    public function __construct(
        public ?int    $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?int    $price_cents = null,
        public ?string $sku = null,
        public bool    $is_active = true,
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
        ];
    }
}
