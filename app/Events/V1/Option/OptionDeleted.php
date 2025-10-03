<?php

namespace App\Events\V1\Option;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

class OptionDeleted extends BaseEvent
{
    public function __construct(
        public readonly int    $option_id,
        public readonly int    $store_id,
        public readonly string $store_sku,
        ?int    $sender_device_id   = null,
        ?string $sender_device_type = null,
        ?string $correlation_id     = null
    ) {
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
        return 'option.deleted';
    }

    public function broadcastWith(): array
    {
        $data  = $this->baseBroadcastWith();

        $data['store'] = [
            'id'  => $this->store_id,
            'sku' => $this->store_sku
        ];

        $data['subject'] = [
            'type' => 'Option',
            'id'   => $this->option_id
        ];

        $data['data'] = [
            'id' => $this->option_id,
        ];

        $data['meta'] = ['replay' => false];

        return $data;
    }
}
