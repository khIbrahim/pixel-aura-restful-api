<?php

namespace App\DTO\V1\Order;

use App\DTO\V1\Order\ServiceType\DeliveryInfo;
use App\DTO\V1\Order\ServiceType\DineInInfo;
use App\DTO\V1\Order\ServiceType\PickupInfo;
use App\Enum\V1\Order\OrderChannel;
use App\Enum\V1\Order\OrderServiceType;
use App\Enum\V1\Order\OrderStatus;

final class OrderData
{

    /**
     * @param int               $store_id
     * @param OrderChannel      $channel
     * @param OrderServiceType  $service_type
     * @param int               $device_id
     * @param int|null          $created_by
     * @param string            $currency
     * @param OrderItemData[]   $items
     * @param string|null       $special_instructions
     * @param OrderPricing|null $pricing
     * @param string|null       $number
     * @param array|null        $metadata
     * @param DeliveryInfo|null $delivery
     * @param DineInInfo|null   $dine_in
     * @param PickupInfo|null   $pickup
     */
    public function __construct(
        public int              $store_id,
        public OrderChannel     $channel,
        public OrderServiceType $service_type,
        public int              $device_id,
        public ?int             $created_by,
        public string           $currency,
        public array            $items,
        public ?string          $special_instructions = null,
        public ?OrderPricing    $pricing              = null,
        public ?string          $number               = null,
        public ?array           $metadata             = null,
        public ?DeliveryInfo    $delivery             = null,
        public ?DineInInfo      $dine_in              = null,
        public ?PickupInfo      $pickup               = null,
    ){}

    public function toArray(bool $cast = true): array
    {
        return [
            'store_id'             => $this->store_id,
            'channel'              => $this->channel->value,
            'service_type'         => $this->service_type->value,
            'device_id'            => $this->device_id,
            'created_by'           => $this->created_by,
            'currency'             => $this->currency,
            'special_instructions' => $this->special_instructions,
            'subtotal_cents'       => $this->pricing?->subtotal_cents,
            'tax_cents'            => $this->pricing?->tax_cents,
            'discount_cents'       => $this->pricing?->discount_cents,
            'total_cents'          => $this->pricing?->total_cents,
            'number'               => $this->number,
            'status'               => OrderStatus::Confirmed->value,
            'metadata'             => $this->metadata,
            'items'                => array_map(fn(OrderItemData $item) => $item->toArray(), $this->items),
            'confirmed_at'         => now()->toDateTimeString(),
            'delivery'             => $cast ? ($this->delivery?->toArray() ?? null) : $this->delivery,
            'dine_in'              => $cast ? ($this->dine_in?->toArray() ?? null) : $this->dine_in,
            'pickup'               => $cast ? ($this->pickup?->toArray() ?? null) : $this->pickup,
        ];
    }

}
