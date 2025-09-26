<?php

namespace App\DTO\V1\OptionList;

use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateOptionListDTO implements Arrayable
{

    public function __construct(
        public string  $name,
        public int     $store_id,
        public ?string $description,
        public ?string $sku,
        public int     $min_selections = 0,
        public ?int    $max_selections = null,
        public bool    $is_active = true,
    ){}

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'store_id'       => $this->store_id,
            'description'    => $this->description,
            'sku'            => $this->sku,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'is_active'      => $this->is_active,
        ];
    }
}
