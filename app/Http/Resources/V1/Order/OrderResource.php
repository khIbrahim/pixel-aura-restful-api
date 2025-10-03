<?php

namespace App\Http\Resources\V1\Order;

use App\Enum\V1\Order\OrderServiceType;
use App\Enum\V1\Order\OrderStatus;
use App\Http\Resources\V1\DeviceResource;
use App\Http\Resources\V1\StoreMemberResource;
use App\Http\Resources\V1\StoreResource;
use App\Models\V1\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'number'  => $this->number,
            'status'  => $this->status->value,
            'channel' => $this->channel->value,
            'service' => $this->getServiceData(),

            'pricing' => [
                'subtotal' => $this->getMoney('subtotal_cents')->toArray(),
                'tax'      => $this->getMoney('tax_cents')->toArray(),
                'discount' => $this->getMoney('discount_cents')->toArray(),
                'total'    => $this->getMoney('total_cents')->toArray(),
                'currency' => $this->currency,
            ],

            'items'       => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when(
                $this->relationLoaded('items'),
                fn() => $this->items->count(),
                0
            ),

            'preparation' => [
                'progress_percentage' => $this->getPreparationProgress(),
                'estimated_ready_at'  => $this->when($this->getEstimatedReadyTime() !== null, fn() => $this->getEstimatedReadyTime()?->toIso8601String()),
                'total_time_minutes' => $this->when($this->relationLoaded('items'), fn() => $this->items->sum(fn($item) => $item->getPreparationTime() * $item->quantity)),
            ],

            'device'  => new DeviceResource($this->whenLoaded('device')),
            'creator' => new StoreMemberResource($this->whenLoaded('creator')),
            'store'   => new StoreResource($this->whenLoaded('store')),

            'special_instructions' => $this->special_instructions,
            'metadata'             => $this->metadata ?? [],

            'timestamps' => [
                'confirmed_at' => $this->confirmed_at?->toIso8601String(),
                'preparing_at' => $this->preparing_at?->toIso8601String(),
                'ready_at'     => $this->ready_at?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
                'cancelled_at' => $this->cancelled_at?->toIso8601String(),
                'created_at'   => $this->created_at->toIso8601String(),
                'updated_at'   => $this->updated_at->toIso8601String(),
            ],

            'available_actions' => $this->getAvailableActions(),
        ];
    }

    private function getServiceData(): array
    {
        $baseData = [
            'type' => $this->service_type->value,
        ];

        $details = match($this->service_type) {
            OrderServiceType::Delivery => $this->getDeliveryDetails(),
            OrderServiceType::DineIn   => $this->getDineInDetails(),
            OrderServiceType::Pickup   => $this->getPickupDetails(),
        };

        if ($details !== null) {
            $baseData['details'] = $details;
        }

        return $baseData;
    }

    private function getDeliveryDetails(): ?array
    {
        if ($this->delivery === null) {
            return null;
        }

        return [
            'address' => $this->delivery->address,
            'contact' => [
                'name'  => $this->delivery->contact_name,
                'phone' => $this->delivery->contact_phone,
            ],
            'notes' => $this->delivery->notes,
            'fee'   => $this->getMoney($this->delivery->fee_cents)->toArray(),
        ];
    }

    private function getDineInDetails(): ?array
    {
        if ($this->dine_in === null) {
            return null;
        }

        return [
            'table_number' => $this->dine_in->table_number,
            'guests_count' => $this->dine_in->number_of_guests,
            'server'       => $this->when($this->dine_in->server_id && $this->relationLoaded('creator'), fn() => new StoreMemberResource($this->creator)),
        ];
    }

    private function getPickupDetails(): ?array
    {
        if ($this->pickup === null) {
            return null;
        }

        return [
            'contact_name'       => $this->pickup->contact_name,
            'estimated_ready_at' => $this->getEstimatedReadyTime()?->toIso8601String(),
        ];
    }

    private function getAvailableActions(): array
    {
        $actions = [];

        if ($this->canBeCancelled()) {
            $actions[] = [
                'action' => 'cancel',
                'label'  => 'Annuler',
                'destructive' => true,
            ];
        }

        if ($this->canBeModified()) {
            $actions[] = [
                'action'      => 'modify',
                'label'       => 'Modifier',
                'destructive' => false,
            ];
        }

        if ($this->canBeCompleted()) {
            $actions[] = [
                'action'      => 'complete',
                'label'       => 'Terminer',
                'destructive' => false,
            ];
        }

        if ($this->canBeRefunded()) {
            $actions[] = [
                'action'      => 'refund',
                'label'       => 'Rembourser',
                'destructive' => true,
            ];
        }

        return $actions;
    }

    private function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::Confirmed,
            OrderStatus::Preparing,
        ]);
    }

    private function canBeRefunded(): bool
    {
        return $this->status === OrderStatus::Completed;
    }

    private function canBeCompleted(): bool
    {
        return $this->status === OrderStatus::Ready;
    }

    private function canBeModified(): bool
    {
        return $this->status === OrderStatus::Confirmed;
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version'   => 'v1',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
