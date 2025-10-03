<?php

namespace App\Http\Resources\V1\Order;

use App\Models\V1\OrderItem;
use App\ValueObjects\V1\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'item' => [
                'id'          => $this->item_id,
                'name'        => $this->item_name,
                'description' => $this->item_description,
                'image_url'   => $this->item_image_url,
                'sku'         => $this->item_sku,
            ],

            'variant'  => $this->when($this->variant_id, [
                'id'   => $this->variant_id,
                'name' => $this->variant_name,
            ]),

            'quantity'                 => $this->quantity,
            'selected_options'         => $this->formatSelectedOptions(),
            'ingredient_modifications' => $this->formatIngredientModifications(),

            'pricing' => [
                'base_price'        => Money::ofMinor($this->base_price_cents, $this->order->currency)->toArray(),
                'options_price'     => Money::ofMinor($this->options_price_cents, $this->order->currency)->toArray(),
                'ingredients_price' => Money::ofMinor($this->ingredients_price_cents, $this->order->currency)->toArray(),
                'item_total'        => Money::ofMinor($this->item_total_cents, $this->order->currency)->toArray(),
                'final_total'       => Money::ofMinor($this->final_total_cents, $this->order->currency)->toArray(),
            ],

            'special_instructions' => $this->special_instructions,
        ];
    }

    private function formatSelectedOptions(): array
    {
        return array_map(function ($option) {
            return [
                'id'                => $option['option_id'] ?? null,
                'name'              => $option['name'] ?? 'Unknown',
                'description'       => $option['description'] ?? '',
                'quantity'          => $option['quantity'] ?? 1,
                'unit_price'        => Money::ofMinor($option['unit_price_cents'] ?? 0, $this->order->currency)->toArray(),
                'total_price_cents' => Money::ofMinor($option['total_price_cents'] ?? 0, $this->order->currency)->toArray(),
            ];
        }, $this->selected_options);
    }

    private function formatIngredientModifications(): array
    {
        return array_map(function ($modification) {
            return [
                'id'                => $modification['ingredient_id'] ?? null,
                'name'              => $modification['name'] ?? 'Unknown',
                'description'       => $modification['description'] ?? '',
                'action'            => $modification['action'] ?? 'add',
                'quantity'          => $modification['quantity'] ?? 0,
                'unit_price'        => Money::ofMinor($modification['unit_price_cents'] ?? 0, $this->order->currency)->toArray(),
                'total_price_cents' => Money::ofMinor($modification['total_price_cents'] ?? 0, $this->order->currency)->toArray(),
            ];
        }, $this->ingredient_modifications);
    }
}
