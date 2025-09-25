<?php

namespace App\Events\V1\Category;

use App\Http\Resources\V1\CategoryResource;
use App\Models\V1\Category;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CategoryCreated implements ShouldBroadcast
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
        return 'category.created';
    }

    public function broadcastWith(): array
    {
        return new CategoryResource($this->category)->resolve();
    }
}
