<?php

namespace App\Events\V1\OptionList;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

class OptionListDeleted extends BaseEvent
{

    public function __construct(
        public readonly int     $optionList_id,
        public readonly int     $store_id,
        public readonly string  $store_sku,
        public readonly ?int    $sender_device_id   = null,
        public readonly ?string $sender_device_type = null,
        public readonly ?string $correlation_id     = null
    ) {
        parent::__construct($this->sender_device_id, $this->sender_device_type, $this->correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->store_id . '.catalog')
        ];
    }

    public function broadcastAs(): string
    {
        return 'OptionListDeleted';
    }

    public function broadcastWith(): array
    {
        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $this->store_id,
                'sku' => $this->store_sku
            ],
            'subject' => [
                'type' => 'OptionList',
                'id'   => $this->optionList_id
            ],
            'data' => [
                'option_list_id' => $this->optionList_id,
                'deleted_at'     => $this->occurred_at
            ],
        ]);
    }
}
