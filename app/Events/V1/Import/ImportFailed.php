<?php

namespace App\Events\V1\Import;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $importClass;
    private string $errorMessage;
    private int    $storeId;

    public function __construct(string $importClass, string $errorMessage, int $storeId)
    {
        $this->importClass  = $importClass;
        $this->errorMessage = $errorMessage;
        $this->storeId      = $storeId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('import.store.' . $this->storeId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'import.failed';
    }

    public function broadcastWith(): array
    {
        return [
            'success'        => false,
            'message'        => $this->errorMessage,
            'importer_class' => $this->importClass,
            'error_message'  => $this->errorMessage,
            'store_id'       => $this->storeId,
        ];
    }
}
