<?php

namespace App\Events\V1\Printing;

use App\Events\V1\BaseEvent;
use App\Models\V1\PrintJob;
use Illuminate\Broadcasting\PrivateChannel;

class PrintJobQueued extends BaseEvent
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
        return 'print_job.queued';
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
            'print_job' => [
                'id'               => $this->printJob->id,
                'type'             => $this->printJob->type,
                'target_printer_id'=> $this->printJob->printer_id,
                'order_id'         => $this->printJob->order_id
            ]
        ];

        $data['meta'] = ['replay' => false];

        return $data;
    }
}
