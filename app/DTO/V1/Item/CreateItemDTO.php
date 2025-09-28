<?php

namespace App\DTO\V1\Item;

use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\ItemVariant\CreateItemVariantDTO;
use App\DTO\V1\Option\CreateOptionDTO;
use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateItemDTO implements Arrayable
{
    /**
     * @param  CreateOptionDTO[]|null  $options
     * @param  CreateItemVariantDTO[]|null  $variants
     * @param  CreateIngredientDTO[]|null  $ingredients
     */
    public function __construct(
        public string $name,
        public int $store_id,
        public ?int $category_id,
        public int $base_price_cents,
        public int $current_cost_cents,
        public ?string $currency = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $description = null,
        public bool $is_active = true,
        public bool $track_inventory = false,
        public ?int $stock = null,
        public bool $loyalty_eligible = false,
        public ?int $age_restriction = null,
        public ?int $reorder_level = null,
        public ?int $weight_grams = null,
        public ?array $tags = null,
        public ?array $metadata = null,
        public ?array $options = null,
        public ?array $variants = null,
        public ?array $ingredients = null,
        public ?int $tax_id = null,
        public ?int $created_by = null,
        public ?string $image = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'base_price_cents' => $this->base_price_cents,
            'current_cost_cents' => $this->current_cost_cents,
            'currency' => $this->currency,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'track_inventory' => $this->track_inventory,
            'stock' => $this->stock,
            'loyalty_eligible' => $this->loyalty_eligible,
            'age_restriction' => $this->age_restriction,
            'reorder_level' => $this->reorder_level,
            'weight_grams' => $this->weight_grams,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
            'options' => array_map(fn ($option) => $option->toArray(), (array) $this->options),
            'variants' => array_map(fn ($variant) => $variant->toArray(), (array) $this->variants),
            'ingredients' => array_map(fn ($ingredient) => $ingredient->toArray(), (array) $this->ingredients),
            'tax_id' => $this->tax_id,
            'created_by' => $this->created_by,
            'image' => $this->image,
        ];
    }
}
