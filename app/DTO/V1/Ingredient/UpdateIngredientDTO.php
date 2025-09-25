<?php

namespace App\DTO\V1\Ingredient;

use Illuminate\Contracts\Support\Arrayable;

final class UpdateIngredientDTO implements Arrayable
{

    public function __construct(
        public ?string $name = null,
        public bool    $is_allergen = false,
        public bool    $is_mandatory = false,
        public bool    $is_active = true,
        public ?int    $item_id = null,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            name         : $data['name'] ?? '',
            is_allergen  : $data['is_allergen'] ?? false,
            is_mandatory : $data['is_mandatory'] ?? false,
            is_active    : $data['is_active'] ?? true,
            item_id      : $data['item_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'         => $this->name,
            'is_allergen'  => $this->is_allergen,
            'is_mandatory' => $this->is_mandatory,
            'is_active'    => $this->is_active,
            'item_id'      => $this->item_id,
        ];
    }
}
