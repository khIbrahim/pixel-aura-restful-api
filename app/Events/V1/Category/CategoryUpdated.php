<?php

namespace App\Events\V1\Category;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\CategoryResource;
use App\Models\V1\Category;
use Illuminate\Broadcasting\PrivateChannel;

class CategoryUpdated extends BaseEvent
{

    public function __construct(public Category $category, ?int $sender_device_id = null, ?string $sender_device_type = null, ?string $correlation_id = null)
    {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->category->store_id . '.catalog'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CategoryUpdated';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $this->category->store->id,
                'sku' => $this->category->store->sku,
            ],
            'data' => new CategoryResource($this->category),
        ]);
    }
}
