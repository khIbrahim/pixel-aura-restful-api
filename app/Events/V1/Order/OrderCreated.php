<?php

namespace App\Events\V1\Order;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\Order\OrderResource;
use App\Models\V1\Order;
use Illuminate\Broadcasting\PrivateChannel;

final class OrderCreated extends BaseEvent
{

    public function __construct(
        public readonly Order      $order,
        ?int       $sender_device_id   = null,
        ?string    $sender_device_type = null,
        ?string    $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->order->store_id . '.orders')
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $this->order->store->id,
                'sku' => $this->order->store->sku,
            ],
            'subject' => [
                'type' => 'Order',
                'id'   => $this->order->id,
            ],
            'data'  => new OrderResource($this->order)
        ]);
    }
}
