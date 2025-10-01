<?php

namespace App\Events\V1\ItemVariant;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

final class ItemVariantDeleted extends BaseEvent
{

    public function __construct(
        public readonly int     $itemVariant_id,
        public readonly int     $item_id,
        public readonly int     $store_id,
        public readonly string  $store_sku,
        public readonly ?int    $sender_device_id   = null,
        public readonly ?string $sender_device_type = null,
        public readonly ?string $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->store_id . '.catalog')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ItemVariantDeleted';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $this->store_id,
                'sku' => $this->store_sku
            ],
            'subject' => [
                'type' => 'ItemVariant',
                'id'   => $this->itemVariant_id
            ],
            'data' => [
                'item_variant_id' => $this->itemVariant_id,
                'item_id'         => $this->item_id,
                'deleted_at'      => $this->occurred_at
            ],
        ]);
    }
}
