<?php

namespace App\Events\V1;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

abstract class BaseEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly string $event_id;
    public readonly string $occurred_at;

    public function __construct(
        public ?int    $sender_device_id   = null,
        public ?string $sender_device_type = null,
        public ?string $correlation_id     = null
    ) {
        $this->event_id    = (string) Str::uuid();
        $this->occurred_at = now()->toIso8601String();
    }

    abstract public function broadcastOn(): array;
    abstract public function broadcastAs(): string;
    abstract public function broadcastWith(): array;

    protected function baseBroadcastWith(): array
    {
        return [
            'v'           => 1,
            'event_id'    => $this->event_id,
            'occurred_at' => $this->occurred_at,
            'sender'      => [
                'device_id'   => $this->sender_device_id,
                'device_type' => $this->sender_device_type
            ],
            'correlation_id' => $this->correlation_id,
            'event'          => static::broadcastAs(),
            'seq'            => null,
        ];
    }

}
