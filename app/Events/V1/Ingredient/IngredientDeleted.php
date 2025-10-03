<?php

namespace App\Events\V1\Ingredient;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

final class IngredientDeleted extends BaseEvent
{

    public function __construct(
        public readonly int     $ingredient_id,
        public readonly int     $store_id,
        public?int    $sender_device_id = null,
        public?string $sender_device_type = null,
        public?string $correlation_id = null
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
        return 'IngredientDeleted';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store'       => [
                'id'      => $this->store_id,
            ],
            'subject' => [
                'type' => 'Ingredient',
                'id'   => $this->ingredient_id
            ],
            'data' => [
                'ingredient_id' => $this->ingredient_id,
                'deleted_at'    => $this->occurred_at
            ],
        ]);
    }
}
