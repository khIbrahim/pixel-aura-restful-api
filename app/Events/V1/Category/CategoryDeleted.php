<?php

namespace App\Events\V1\Category;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

final class CategoryDeleted extends BaseEvent
{

    public function __construct(
        public int $categoryId,
        public int $store_id,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->store_id . '.catalog'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CategoryDeleted';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store'       => [
                'id'      => $this->store_id,
            ],
            'subject' => [
                'type' => 'Category',
                'id'   => $this->categoryId
            ],
            'data' => [
                'category_id' => $this->categoryId,
                'deleted_at'  => $this->occurred_at
            ],
        ]);
    }
}
