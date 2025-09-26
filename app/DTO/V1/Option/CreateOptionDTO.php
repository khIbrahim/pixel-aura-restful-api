<?php

namespace App\DTO\V1\Option;

use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateOptionDTO implements Arrayable
{
    public function __construct(
        public int     $store_id,
        public ?int    $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?int    $price_cents = null,
        public bool    $is_active = true,
        public ?int    $option_list_id = null,
    ) {}

    public function toArray(): array
    {
        return [
            'store_id'       => $this->store_id,
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'price_cents'    => $this->price_cents,
            'is_active'      => $this->is_active,
            'option_list_id' => $this->option_list_id,
        ];
    }
}
