<?php

namespace App\DTO\V1\Ingredient;

use App\DTO\V1\Abstract\AbstractPivotDTO;

class IngredientPivotDTO extends AbstractPivotDTO
{

    public function __construct(
        public int $ingredientId,
        public int $storeId,
        public ?string $name,
        public ?string $description,
        public ?int $costPerUnitCents,
        public ?string $unit,
        public ?bool $isActive,
        public ?bool $isMandatory,
        public ?bool $isAllergen,
    ){}

    public function getPivotKey(): int|string
    {
        return $this->ingredientId;
    }

    public function getPivotData(): array
    {
        return [
            'store_id'             => $this->storeId,
            'name'                 => $this->name,
            'description'          => $this->description,
            'cost_per_unit_cents'  => $this->costPerUnitCents,
            'unit'                 => $this->unit,
            'is_active'            => $this->isActive,
            'is_mandatory'         => $this->isMandatory,
            'is_allergen'          => $this->isAllergen,
        ];
    }
}
