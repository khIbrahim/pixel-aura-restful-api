<?php

namespace App\Events\V1\ItemVariant;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\ItemVariantResource;
use App\Models\V1\ItemVariant;
use Illuminate\Broadcasting\PrivateChannel;

final class ItemVariantCreated extends BaseEvent
{

    public function __construct(
        public readonly ItemVariant $itemVariant,
        public ?int        $sender_device_id   = null,
        public ?string     $sender_device_type = null,
        public ?string     $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->itemVariant->item->store_id . '.catalog')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ItemVariantCreated';
    }

    public function broadcastWith(): array
    {
        $store = $this->itemVariant->item->store;

        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $store->id,
                'sku' => $store->sku
            ],
            'subject' => [
                'type' => 'ItemVariant',
                'id'   => $this->itemVariant->id
            ],
            'data' => new ItemVariantResource($this->itemVariant),
        ]);
    }
}
