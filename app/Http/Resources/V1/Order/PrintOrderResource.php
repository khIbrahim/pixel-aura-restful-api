<?php

namespace App\Http\Resources\V1\Order;

use App\Models\V1\Order;
use App\Models\V1\OrderItem;
use App\ValueObjects\V1\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class PrintOrderResource extends JsonResource
{

    public function __construct(Order $resource, private readonly array $printConfig = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->number,
            'order_type'   => $this->service_type->value,
            'created_at'   => $this->created_at?->toISOString(),

            'store'        => [
                'name'     => $this->store->name,
                'address'  => $this->store->address,
                'phone'    => $this->store->phone,
                'email'    => $this->store->email,
            ],

            'delivery'     => $this->delivery?->toArray(),
            'items'        => $this->whenLoaded('items', function() {
                return $this->items->map(function(OrderItem $item) {
                    return [
                        'name'        => $item->item_name ?? $item->item?->name,
                        'quantity'    => $item->quantity,
                        'unit_price'  => Money::ofMinor($item->base_price_cents, $this->currency)->formatted(),
                        'total_price' => Money::ofMinor($item->final_total_cents, $this->currency)->formatted(),
                        'notes'       => $item->special_instructions,

                        'options'     => array_map(function ($option) {
                            return [
                                'name'     => $option['name'] ?? 'Unknown',
                                'quantity' => $option['quantity'] ?? 1,
                                'price'    => Money::ofMinor($option['total_price_cents'] ?? 0, $this->currency)->formatted(),
                                'action'   => ($option['quantity'] ?? 1) > 0 ? 'Add' : 'Remove',
                            ];
                        }, $item->selected_options ?? []),

                        'ingredients' => array_map(function ($modification) {
                            return [
                                'name'     => $modification['name'] ?? 'Unknown',
                                'action'   => $modification['action'] ?? 'add',
                                'quantity' => $modification['quantity'] ?? 0,
                                'price'    => Money::ofMinor($modification['total_price_cents'] ?? 0, $this->currency)->formatted(),
                            ];
                        }, $item->ingredient_modifications ?? [])
                    ];
                })->toArray();
            }, []),
            'pricing'      => [
                'subtotal'     => $this->getMoney('subtotal_cents')->formatted(),
                'tax'          => $this->getMoney('tax_cents')->formatted(),
                'discount'     => $this->getMoney('discount_cents')->formatted(),
                'delivery_fee' => Money::ofMinor($this->delivery?->fee_cents ?? 0, $this->currency)->formatted(),
                'total'        => $this->getMoney('total_cents')->formatted(),
                'currency'     => $this->currency,
            ],

            'notes'          => $this->special_instructions,
            'payement_method' => $this->metadata['payment_method'] ?? 'Cash',
            'creator'        => $this->creator?->name,

            'print' => $this->printConfig
        ];
    }
}
