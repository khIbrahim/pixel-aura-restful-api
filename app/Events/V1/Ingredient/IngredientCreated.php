<?php

namespace App\Events\V1\Ingredient;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\IngredientResource;
use App\Models\V1\Ingredient;
use Illuminate\Broadcasting\PrivateChannel;

final class IngredientCreated extends BaseEvent
{

    public function __construct(
        public readonly Ingredient $ingredient,
        public ?int $sender_device_id = null,
        public ?string $sender_device_type = null,
        public ?string $correlation_id = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->ingredient->store_id . '.catalog')
        ];
    }

    public function broadcastAs(): string
    {
        return 'IngredientCreated';
    }

    public function broadcastWith(): array
    {
        $store = $this->ingredient->store;

        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $store->id,
                'sku' => $store->sku
            ],
            'subject' => [
                'type' => 'Ingredient',
                'id'   => $this->ingredient->id
            ],
            'data' => new IngredientResource($this->ingredient)
        ]);
    }
}
