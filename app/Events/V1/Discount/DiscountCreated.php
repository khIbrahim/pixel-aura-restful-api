<?php

namespace App\Events\V1\Discount;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\DiscountResource;
use App\Models\V1\Discount;
use Illuminate\Broadcasting\PrivateChannel;

final class DiscountCreated extends BaseEvent
{

    public function __construct(
        public Discount $discount,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->discount->store_id . '.discounts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DiscountCreated';
    }

    public function broadcastWith(): array
    {
        $data = $this->baseBroadcastWith();

        $data['store'] = [
            'id'  => $this->discount->store_id,
            'sku' => $this->discount->store->sku
        ];

        $data['subject'] = [
            'type' => 'Discount',
            'id'   => $this->discount->id,
            'code' => $this->discount->code,
        ];

        $data['data'] = new DiscountResource($this->discount);

        return $data;
    }
}
