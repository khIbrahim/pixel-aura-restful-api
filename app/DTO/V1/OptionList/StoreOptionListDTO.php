<?php

namespace App\DTO\V1\OptionList;

use Illuminate\Contracts\Support\Arrayable;

final readonly class StoreOptionListDTO implements Arrayable
{

    public function __construct(
        public string  $name,
        public int     $storeId,
        public ?string $description,
        public ?string $sku,
        public int     $min_selections,
        public int     $max_selections,
        public bool    $is_active = true,
    ){}

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'store_id'       => $this->storeId,
            'description'    => $this->description,
            'sku'            => $this->sku,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'is_active'      => $this->is_active,
        ];
    }
}
