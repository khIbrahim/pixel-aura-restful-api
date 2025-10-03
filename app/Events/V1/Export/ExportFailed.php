<?php

namespace App\Events\V1\Export;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

class ExportFailed extends BaseEvent
{

    public function __construct(
        public int $storeId,
        public string $errorMessage,
        public string $exporterClass,
        public ?string $jobId = null,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null,
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
        return 'ExportFailed';
    }

    public function broadcastWith(): array
    {
        return [
            ...$this->baseBroadcastWith(),
            'store'    => [
                'id'  => $this->storeId,
            ],
            'subject'  => [
                'type' => 'Export',
                'id'   => null,
            ],
            'error'    => $this->errorMessage,
            'job_id'   => $this->jobId,
            'exporter' => $this->exporterClass,
        ];
    }

}
