<?php

namespace App\Events\V1\Printing;

use App\Events\V1\BaseEvent;
use App\Models\V1\PrintJob;
use Illuminate\Broadcasting\PrivateChannel;

class PrintJobCompleted extends BaseEvent
{
    public function __construct(
        public readonly PrintJob $printJob,
        ?int    $sender_device_id   = null,
        ?string $sender_device_type = null,
        ?string $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->printJob->store_id . '.printing'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'print_job.completed';
    }

    public function broadcastWith(): array
    {
        $data = $this->baseBroadcastWith();
        $store = $this->printJob->store;

        $data['store'] = [
            'id'  => $store->id,
            'sku' => $store->sku
        ];

        $data['subject'] = [
            'type' => 'PrintJob',
            'id'   => $this->printJob->id
        ];

        $data['data'] = [
            'print_job_id' => $this->printJob->id,
            'completed_at' => $this->occurred_at
        ];

        $data['meta'] = ['replay' => false];

        return $data;
    }
}
