<?php

namespace App\Events\V1\Notification;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\NotificationResource;
use App\Models\V1\Notification;
use Illuminate\Broadcasting\PrivateChannel;

final class NotificationCreated extends BaseEvent
{

    public function __construct(
        public readonly Notification $notification,
        ?int $sender_device_id      = null,
        ?string $sender_device_type = null,
        ?string $correlation_id     = null
    )
    {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->notification->store_id . '.notifications')
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationCreated';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id' => $this->notification->store_id,
            ],
            'subject' => [
                'type' => 'Notification',
                'id'   => $this->notification->id,
            ],
            'data' => new NotificationResource($this->notification),
        ]);
    }
}
