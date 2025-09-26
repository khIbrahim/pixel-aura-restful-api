<?php

namespace App\DTO\V1\Option;

use Illuminate\Contracts\Support\Arrayable;

final class UpdateOptionDTO implements Arrayable
{

    public function __construct(
        public ?string $name           = null,
        public ?string $description    = null,
        public ?int    $price_cents    = null,
        public ?bool   $is_active      = null,
        public ?int    $option_list_id = null,
    ){}

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'description'    => $this->description,
            'price_cents'    => $this->price_cents,
            'is_active'      => $this->is_active,
            'option_list_id' => $this->option_list_id,
        ];
    }
}
