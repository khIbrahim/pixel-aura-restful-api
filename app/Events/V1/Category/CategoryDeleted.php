<?php

namespace App\Events\V1\Category;

use App\Models\V1\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryDeleted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Category $category){}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->category->store_id . '.categories'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'category.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->category->id,
        ];
    }
}
