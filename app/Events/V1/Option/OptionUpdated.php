<?php

namespace App\Events\V1\Option;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\OptionResource;
use App\Models\V1\Option;
use Illuminate\Broadcasting\PrivateChannel;

class OptionUpdated extends BaseEvent
{
    public function __construct(
        public readonly Option $option,
        ?int    $sender_device_id   = null,
        ?string $sender_device_type = null,
        ?string $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->option->store->id . '.catalog'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OptionUpdated';
    }

    public function broadcastWith(): array
    {
        $data  = $this->baseBroadcastWith();
        $store = $this->option->store;

        $data['store'] = [
            'id'  => $store->id,
            'sku' => $store->sku
        ];

        $data['subject'] = [
            'type' => 'Option',
            'id'   => $this->option->id
        ];

        $data['data'] = new OptionResource($this->option);

        $data['meta'] = ['replay' => false];

        return $data;
    }
}
