<?php

namespace App\Events\V1\Export;

use App\Support\Results\ExportResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly ExportResult $result,
        private readonly int $storeId,
    ){}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->storeId . '.exports'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'export.completed';
    }

    public function broadcastWith(): array
    {
        return $this->result->toArray();
    }

    public function getResult(): ExportResult
    {
        return $this->result;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }
}
