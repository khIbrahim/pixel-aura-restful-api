<?php

namespace App\Events\V1\Item;

use App\Http\Resources\V1\ItemResource;
use App\Models\V1\Item;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Item $item
    ){}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->item->store_id . '.items'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'item.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'item' => new ItemResource($this->item)->toArray(),
        ];
    }
}
