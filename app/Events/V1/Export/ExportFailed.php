<?php

namespace App\Events\V1\Export;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct(
        private readonly int $storeId,
        private readonly string $errorMessage,
        private readonly string $exporterClass,
        private readonly ?string $jobId = null,
    ){}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->storeId . '.exports'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'export.failed';
    }

    public function broadcastWith(): array
    {
        return [
            'error' => $this->errorMessage,
            'job_id' => $this->jobId,
        ];
    }

}
