<?php

namespace App\Events\V1\Media;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\MediaResource;
use Illuminate\Broadcasting\PrivateChannel;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaCreated extends BaseEvent
{

    public function __construct(
        public readonly Media   $media,
        public readonly int     $store_id,
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
        return 'MediaCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'store' => [
                'id'  => $this->store_id,
            ],
            'subject' => [
                'type' => 'Media',
                'id'   => $this->media->id,
            ],
            'data' =>  new MediaResource($this->media),
        ];
    }
}
