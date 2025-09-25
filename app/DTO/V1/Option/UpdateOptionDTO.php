<?php

namespace App\DTO\V1\Option;

use Illuminate\Contracts\Support\Arrayable;

final class UpdateOptionDTO implements Arrayable
{

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int $price_cents = null,
        public ?bool $is_active = null,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            price_cents: $data['price_cents'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'is_active'   => $this->is_active,
        ];
    }
}
