<?php

namespace App\Events\V1\Export;

use App\Events\V1\BaseEvent;
use App\Support\Results\ExportResult;
use Illuminate\Broadcasting\PrivateChannel;
class ExportCompleted extends BaseEvent
{

    public function __construct(
        public ExportResult $result,
        public int $storeId,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->storeId . '.export'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ExportCompleted';
    }

    public function broadcastWith(): array
    {
        return [
            'store' => [
                'id' => $this->storeId,
            ],
            'subject' => [
                'type' => 'Export',
                'id'   => null,
            ],
            'data' => $this->result->toArray(),
            ...$this->baseBroadcastWith(),
        ];
    }

}
