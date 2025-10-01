<?php

namespace App\Events\V1\Item;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\ItemResource;
use App\Models\V1\Item;
use Illuminate\Broadcasting\PrivateChannel;

final class ItemUpdated extends BaseEvent
{

    public function __construct(public Item $item, ?int $sender_device_id = null, ?string $sender_device_type = null, ?string $correlation_id = null)
    {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->item->store_id . '.catalog'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ItemUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'store' => [
                'id'   => $this->item->store_id,
                'sku'  => $this->item->store->sku,
            ],
            'subject' => [
                'type' => 'item',
                'id'   => $this->item->id,
                'sku'  => $this->item->sku,
            ],
            'data' => new ItemResource($this->item),
        ];
    }
}
