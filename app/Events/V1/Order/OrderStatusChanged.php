<?php
namespace App\Events\V1\Order;

use App\Enum\V1\Order\OrderStatus;
use App\Events\V1\BaseEvent;
use App\Models\V1\Order;
use Illuminate\Broadcasting\PrivateChannel;

class OrderStatusChanged extends BaseEvent
{

    public function __construct(
        public Order       $order,
        public OrderStatus $oldStatus,
        public OrderStatus $newStatus,
        public array       $preparationData = [],
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.'.$this->order->store_id . '.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusChanged';
    }

    public function broadcastWith(): array
    {
        $data = $this->baseBroadcastWith();

        $data['store'] = [
            'id'  => $this->order->store->id,
            'sku' => $this->order->store->sku,
        ];

        $data['subject'] = [
            'type'   => 'Order',
            'id'     => $this->order->id,
            'number' => $this->order->number,
        ];

        $data['data'] = [
            'old_status'  => $this->oldStatus->value,
            'new_status'  => $this->newStatus->value,
            'preparation' => $this->preparationData,
        ];

        return $data;
    }
}
