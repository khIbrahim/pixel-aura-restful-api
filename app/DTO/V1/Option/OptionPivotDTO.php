<?php

namespace App\DTO\V1\Option;

use App\DTO\V1\Abstract\AbstractPivotDTO;
use App\Models\V1\Option;

readonly class OptionPivotDTO extends AbstractPivotDTO
{
    public function __construct(
        public int     $option_id,
        public int     $store_id,
        public string  $name,
        public ?string $description,
        public int     $price_cents,
        public bool    $is_active
    ) {}

    public function getPivotKey(): int
    {
        return $this->option_id;
    }

    public function getPivotData(): array
    {
        return [
            'store_id'    => $this->store_id,
            'name'        => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'is_active'   => $this->is_active,
        ];
    }

    public static function fromCreation(CreateOptionDTO $data, ?Option $option = null): self
    {
        return new OptionPivotDTO(
            option_id: $data->id ?? $option->id,
            store_id: $data->storeId ?? $option->store_id,
            name: $data->name ?? $option->name,
            description: $data->description ?? $option->description ?? null,
            price_cents: $data->priceCents ?? $option->price_cents ?? 0,
            is_active: $data->isActive ?? $option->is_active ?? true
        );
    }

}
