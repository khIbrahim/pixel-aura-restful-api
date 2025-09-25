<?php

namespace App\DTO\V1\Ingredient;

use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateIngredientDTO implements Arrayable
{
    public function __construct(
        public ?int    $id = null,
        public int     $store_id,
        public ?string $name = null,
        public ?string $description = null,
        public bool    $is_allergen = false,
        public bool    $is_mandatory = true,
        public bool    $is_active = true,
        public ?string $unit = null,
        public int     $cost_per_unit_cents = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: null,
            store_id: $data['store_id'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            is_allergen: $data['is_allergen'] ?? false,
            is_mandatory: $data['is_mandatory'] ?? false,
            is_active: $data['is_active'] ?? false,
            unit: $data['unit'] ?? null,
            cost_per_unit_cents: $data['cost_per_unit_cents'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'store_id'            => $this->store_id,
            'name'                => $this->name,
            'description'         => $this->description,
            'is_allergen'         => $this->is_allergen,
            'is_mandatory'        => $this->is_mandatory,
            'unit'                => $this->unit,
            'cost_per_unit_cents' => $this->cost_per_unit_cents,
            'is_active'           => $this->is_active,
        ];
    }
}
