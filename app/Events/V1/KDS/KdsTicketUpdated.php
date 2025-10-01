<?php

namespace App\Events\V1\KDS;

use App\Events\V1\BaseEvent;
use App\Models\V1\KdsTicket;
use Illuminate\Broadcasting\PrivateChannel;

class KdsTicketUpdated extends BaseEvent
{
    public function __construct(
        public readonly KdsTicket $ticket,
        public readonly ?string $previous_status = null,
        ?int    $sender_device_id   = null,
        ?string $sender_device_type = null,
        ?string $correlation_id     = null
    ) {
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->ticket->order->store_id . '.kds'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kds.ticket_updated';
    }

    public function broadcastWith(): array
    {
        $data = $this->baseBroadcastWith();
        $store = $this->ticket->order->store;

        $data['store'] = [
            'id'  => $store->id,
            'sku' => $store->sku
        ];

        $data['subject'] = [
            'type' => 'KdsTicket',
            'id'   => $this->ticket->id
        ];

        $data['data'] = [
            'ticket_id' => $this->ticket->id,
            'status'    => $this->ticket->status,
            'at'        => $this->occurred_at
        ];

        if ($this->previous_status) {
            $data['data']['from_status'] = $this->previous_status;
        }

        $data['meta'] = ['replay' => false];

        return $data;
    }
}
