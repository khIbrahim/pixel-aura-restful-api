<?php

namespace App\DTO\V1\Item;

use App\DTO\V1\Abstract\BaseDTO;

final readonly class UpdateItemDTO extends BaseDTO
{
    public function __construct(
        public int $id,
        public int $store_id,
        public ?int $category_id = null,
        public ?int $tax_id = null,
        public ?string $name = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $description = null,
        public ?string $currency = null,
        public ?int $base_price_cents = null,
        public ?int $current_cost_cents = null,
        public ?bool $is_active = null,
        public ?bool $track_inventory = null,
        public ?int $stock = null,
        public ?bool $loyalty_eligible = null,
        public ?int $age_restriction = null,
        public ?int $reorder_level = null,
        public ?int $weight_grams = null,
        public ?array $tags = null,
        public ?array $metadata = null,
        public ?int $updated_by = null,
        public ?string $image = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'category_id' => $this->category_id,
            'tax_id' => $this->tax_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'currency' => $this->currency,
            'base_price_cents' => $this->base_price_cents,
            'current_cost_cents' => $this->current_cost_cents,
            'is_active' => $this->is_active,
            'track_inventory' => $this->track_inventory,
            'stock' => $this->stock,
            'loyalty_eligible' => $this->loyalty_eligible,
            'age_restriction' => $this->age_restriction,
            'reorder_level' => $this->reorder_level,
            'weight_grams' => $this->weight_grams,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
            'updated_by' => $this->updated_by,
        ], fn ($value) => $value !== null);
    }
}
