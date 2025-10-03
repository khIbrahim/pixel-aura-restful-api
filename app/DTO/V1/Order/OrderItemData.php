<?php

namespace App\DTO\V1\Order;

use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use Illuminate\Contracts\Support\Arrayable;

final class OrderItemData implements Arrayable
{

    /**
     * @param int $item_id
     * @param int|null $variant_id
     * @param int $quantity
     * @param OrderOptionData[] $options
     * @param OrderIngredientData[] $modifications
     * @param string|null $special_instructions
     * @param Item|null $item
     * @param ItemVariant|null $itemVariant
     * @param ItemPricing|null $pricing
     */
    public function __construct(
        public int          $item_id,
        public ?int         $variant_id,
        public int          $quantity,
        public array        $options,
        public array        $modifications,
        public ?string      $special_instructions = null,
        public ?Item        $item                 = null,
        public ?ItemVariant $itemVariant          = null,
        public ?ItemPricing $pricing              = null,
    ){}

    public function setPricing(ItemPricing $pricing): self
    {
        $clone = clone $this;
        $clone->pricing = $pricing;
        return $clone;
    }

    public function getBasePriceCents(): int
    {
        return $this->itemVariant?->price_cents ?? $this->item?->base_price_cents ?? 0;
    }

    public function toArray(): array
    {
        return [
            'item_id'                  => $this->item_id,
            'item_name'                => $this->item?->name ?? null,
            'item_description'         => $this->item?->description ?? null,
            'item_image_url'           => $this->item?->getThumbnailUrl() ?? null,
            'item_sku'                 => $this->item?->sku ?? null,
            'variant_id'               => $this->variant_id,
            'variant_name'             => $this->itemVariant?->name ?? null,
            'base_price_cents'         => $this->getBasePriceCents(),
            'selected_options'         => ! empty($this->options) ? array_map(fn (OrderOptionData $optionData) => $optionData->toArray(), $this->options) : [],
            'ingredient_modifications' => ! empty($this->modifications) ? array_map(fn (OrderIngredientData $ingredientData) => $ingredientData->toArray(), $this->modifications) : [],
            'options_price_cents'      => $this->pricing?->options_price_cents,
            'ingredients_price_cents'  => $this->pricing?->modifications_price_cents,
            'item_total_cents'         => $this->pricing?->item_total_cents,
            'quantity'                 => $this->quantity,
            'final_total_cents'        => $this->pricing?->final_total_cents,
            'special_instructions'     => $this->special_instructions,
        ];
    }
}
