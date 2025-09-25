<?php

namespace App\DTO\V1\OptionList;

use Illuminate\Contracts\Support\Arrayable;

final readonly class UpdateOptionListDTO implements Arrayable
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int    $min_selections = null,
        public ?int    $max_selections = null,
        public ?bool   $is_active = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name'           => $this->name,
            'description'    => $this->description,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'is_active'      => $this->is_active,
        ], fn($value) => $value !== null);
    }
}
