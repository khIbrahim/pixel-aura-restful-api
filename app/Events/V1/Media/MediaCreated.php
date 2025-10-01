<?php

namespace App\Events\V1\Media;

use App\Http\Resources\V1\MediaResource;
use App\Models\V1\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly string $event_id;
    public readonly string $occurred_at;

    public function __construct(
        public readonly Media   $media,
        public readonly int     $store_id,
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
        return 'MediaCreated';
    }

    public function broadcastWith(): array
    {
        $store = Store::query()->find($this->store_id);

        return [
            'v'           => 1,
            'event'       => 'MediaCreated',
            'event_id'    => $this->event_id,
            'seq'         => null,
            'occurred_at' => $this->occurred_at,
            'store' => [
                'id'  => $store->id,
                'sku' => $store->sku
            ],
            'sender' => [
                'device_id'   => $this->sender_device_id,
                'device_type' => $this->sender_device_type
            ],
            'subject' => [
                'type' => 'Media',
                'id'   => $this->media->id
            ],
            'correlation_id' => $this->correlation_id,
            'data' => [
                'media' => new MediaResource($this->media)->resolve()
            ],
            'meta' => [
                'replay' => false
            ]
        ];
    }
}
