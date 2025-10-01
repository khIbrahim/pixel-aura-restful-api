<?php

namespace App\Events\V1\Item;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

final class ItemDeleted extends BaseEvent
{

    public function __construct(
        public int $itemId,
        public int $storeId,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->storeId . '.catalog'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ItemDeleted';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id' => $this->storeId,
            ],
            'subject' => [
                'type' => 'item',
                'id'   => $this->itemId,
            ],
            'data' => [
                'id'       => $this->itemId,
                'store_id' => $this->storeId,
            ]
        ]);
    }

}
