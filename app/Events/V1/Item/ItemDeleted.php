<?php

namespace App\Events\V1\Item;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $itemId,
        public int $storeId
    ){}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->storeId . '.items'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'item.created';
    }

    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->itemId,
        ];
    }

}
