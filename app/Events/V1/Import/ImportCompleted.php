<?php

namespace App\Events\V1\Import;

use App\Support\Results\ImportResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ImportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ImportResult $result;
    public int $storeId;

    public function __construct(ImportResult $result, int $storeId)
    {
        $this->result  = $result;
        $this->storeId = $storeId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("import.store.$this->storeId"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'import.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'result' => [
                'success'         => $this->result->isSuccess(),
                'message'         => $this->result->getMessage(),
                'total'           => $this->result->getTotal(),
                'imported'        => $this->result->getImported(),
                'skipped'         => $this->result->getSkipped(),
                'error_count'     => $this->result->getErrorCount(),
                'process_time_ms' => $this->result->getProcessTimeMs(),
            ],
        ];
    }
}
