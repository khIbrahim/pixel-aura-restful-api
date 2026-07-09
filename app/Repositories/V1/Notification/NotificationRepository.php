<?php

namespace App\Repositories\V1\Notification;

use App\Contracts\V1\Notification\NotificationRepositoryInterface;
use App\Models\V1\Notification;
use App\Repositories\V1\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{

    public function list(int $storeId, bool $unread, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->orderByDesc('created_at');

        if($unread){
            $query->whereNull('read_at');
        }

        return $query->paginate($perPage);
    }

    public function markAsRead(int $storeId, int $notificationId): Notification
    {
        /** @var Notification $notification */
        $notification = $this->query()
            ->where('store_id', $storeId)
            ->where('id', $notificationId)
            ->firstOrFail();

        if($notification->read_at === null){
            $notification->update([
                'read_at' => now()
            ]);
        }

        return $notification->refresh();
    }

    public function markAllAsRead(int $storeId): int
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now()
            ]);
    }

    public function deleteForStore(int $storeId, int $notificationId): void
    {
        $this->query()
            ->where('store_id', $storeId)
            ->where('id', $notificationId)
            ->delete();
    }

    public function model(): string
    {
        return Notification::class;
    }

}
