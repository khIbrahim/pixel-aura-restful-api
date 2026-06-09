<?php

namespace App\DTO\V1\OptionList;

use Illuminate\Contracts\Support\Arrayable;

final readonly class AttachOptionListDTO implements Arrayable
{
    public function __construct(
        public int $id,
        public int $store_id,
        public bool $is_required = false,
        public int $min_selections = 0,
        public ?int $max_selections = null,
        public ?int $display_order = null,
        public bool $is_active = true,
    ) {}

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'store_id'       => $this->store_id,
            'is_required'    => $this->is_required,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'display_order'  => $this->display_order,
            'is_active'      => $this->is_active,
        ];
    }
}
