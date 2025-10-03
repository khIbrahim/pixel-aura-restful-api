<?php

namespace App\Events\V1\Import;

use App\Events\V1\BaseEvent;
use Illuminate\Broadcasting\PrivateChannel;

class ImportFailed extends BaseEvent
{

    public function __construct(
        public string $importClass,
        public string $errorMessage,
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
            new PrivateChannel('store.' . $this->storeId . '.import'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ImportFailed';
    }

    public function broadcastWith(): array
    {
        $result = [
            'success'        => false,
            'message'        => $this->errorMessage,
            'importer_class' => $this->importClass,
            'error_message'  => $this->errorMessage,
            'store_id'       => $this->storeId,
        ];

        $data = $this->baseBroadcastWith();
        $data['store'] = [
            'id' => $this->storeId,
        ];

        $data['context'] = [
            'type' => 'Import',
            'id'   => null
        ];

        $data['data'] = $result;

        return $data;
    }
}
