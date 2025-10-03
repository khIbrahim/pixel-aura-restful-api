<?php

namespace App\Events\V1\Media;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

class MediaDeleted extends BaseEvent
{

    public function __construct(
        public readonly int     $media_id,
        public readonly int     $store_id,
        public readonly string  $store_sku,
        public ?int    $sender_device_id   = null,
        public ?string $sender_device_type = null,
        public ?string $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->store_id . '.media')
        ];
    }

    public function broadcastAs(): string
    {
        return 'MediaDeleted';
    }

    public function broadcastWith(): array
    {
        return [
            ...$this->baseBroadcastWith(),
            'store' => [
                'id'  => $this->store_id,
                'sku' => $this->store_sku
            ],
            'subject' => [
                'type' => 'Media',
                'id'   => $this->media_id
            ],
            'data' => [
                'media_id'   => $this->media_id,
                'deleted_at' => $this->occurred_at
            ],
            'meta' => [
                'replay' => false
            ]
        ];
    }
}
