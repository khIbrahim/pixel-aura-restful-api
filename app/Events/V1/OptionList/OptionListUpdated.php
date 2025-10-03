<?php

namespace App\Events\V1\OptionList;

use App\Events\V1\BaseEvent;
use App\Http\Resources\V1\OptionListResource;
use App\Models\V1\OptionList;
use Illuminate\Broadcasting\PrivateChannel;

final class OptionListUpdated extends BaseEvent
{

    public function __construct(
        public readonly OptionList $optionList,
        public readonly array      $changes = [],
        ?int       $sender_device_id   = null,
        ?string    $sender_device_type = null,
        ?string    $correlation_id     = null
    ) {
        parent::__construct($this->sender_device_id, $this->sender_device_type, $this->correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->optionList->store_id . '.catalog')
        ];
    }

    public function broadcastAs(): string
    {
        return 'OptionListUpdated';
    }

    public function broadcastWith(): array
    {
        $store = $this->optionList->store;

        return array_merge($this->baseBroadcastWith(), [
            'store' => [
                'id'  => $store->id,
                'sku' => $store->sku
            ],
            'subject' => [
                'type' => 'OptionList',
                'id'   => $this->optionList->id
            ],
            'data' => new OptionListResource($this->optionList),
        ]);
    }
}
