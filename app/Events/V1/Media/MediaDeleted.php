<?php

namespace App\Events\V1\Media;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class MediaDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly string $event_id;
    public readonly string $occurred_at;

    public function __construct(
        public readonly int     $media_id,
        public readonly int     $store_id,
        public readonly string  $store_sku,
        public readonly ?int    $sender_device_id   = null,
        public readonly ?string $sender_device_type = null,
        public readonly ?string $correlation_id     = null
    ) {
        $this->event_id = (string) Str::uuid();
        $this->occurred_at = now()->toIso8601String();
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
            'v'           => 1,
            'event'       => 'MediaDeleted',
            'event_id'    => $this->event_id,
            'seq'         => null, // À remplir par le middleware de séquence
            'occurred_at' => $this->occurred_at,
            'store' => [
                'id'  => $this->store_id,
                'sku' => $this->store_sku
            ],
            'sender' => [
                'device_id'   => $this->sender_device_id,
                'device_type' => $this->sender_device_type
            ],
            'subject' => [
                'type' => 'Media',
                'id'   => $this->media_id
            ],
            'correlation_id' => $this->correlation_id,
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
