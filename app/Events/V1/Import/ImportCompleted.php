<?php

namespace App\Events\V1\Import;

use App\Events\V1\BaseEvent;
use App\Support\Results\ImportResult;
use Illuminate\Broadcasting\PrivateChannel;

class ImportCompleted extends BaseEvent
{

    public function __construct(
        public ImportResult $result,
        public int          $storeId,
        ?int $sender_device_id = null,
        ?string $sender_device_type = null,
        ?string $correlation_id = null
    ){
        parent::__construct($sender_device_id, $sender_device_type, $correlation_id);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("store." . $this->storeId . ".import"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ImportCompleted';
    }

    public function broadcastWith(): array
    {
        $data  = $this->baseBroadcastWith();

        $data['store'] = [
            'id'  => $this->storeId,
        ];

        $data['subject'] = [
            'type' => 'Import',
            'id'   => null
        ];

        $data['data'] = [
            'success'         => $this->result->isSuccess(),
            'message'         => $this->result->getMessage(),
            'total'           => $this->result->getTotal(),
            'imported'        => $this->result->getImported(),
            'skipped'         => $this->result->getSkipped(),
            'error_count'     => $this->result->getErrorCount(),
            'process_time_ms' => $this->result->getProcessTimeMs(),
        ];

        return $data;
    }
}
