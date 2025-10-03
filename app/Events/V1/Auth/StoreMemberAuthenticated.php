<?php

namespace App\Events\V1\Auth;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\StoreMemberResource;
use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use Illuminate\Broadcasting\PrivateChannel;

class StoreMemberAuthenticated extends BaseEvent
{

    public function __construct(
        public readonly StoreMember $storeMember,
        public readonly Device $device,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->storeMember->store_id . '.auth'),
        ];
    }

    public function broadcastAs(): string
    {
        return "StoreMemberAuthenticated";
    }

    public function broadcastWith(): array
    {
        $data  = $this->baseBroadcastWith();
        $store = $this->storeMember->store;

        $data['store'] = [
            'id'  => $store->id,
            'sku' => $store->sku
        ];

        $data['subject'] = [
            'type' => 'StoreMember',
            'id'   => $this->storeMember->id
        ];

        $data['data'] = new StoreMemberResource($this->storeMember);

        $data['meta'] = ['replay' => false];

        return $data;
    }
}
