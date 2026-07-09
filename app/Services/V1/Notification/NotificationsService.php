<?php

namespace App\Services\V1\Notification;

use App\Contracts\V1\Notification\NotificationRepositoryInterface;
use App\Contracts\V1\Notification\NotificationsServiceInterface;
use App\Models\V1\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class NotificationsService implements NotificationsServiceInterface
{

    public function __construct(
        private NotificationRepositoryInterface $repository
    ){}

    public function list(int $storeId, bool $unread, int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->list($storeId, $unread, $perPage);
    }

    public function markAsRead(int $storeId, int $notificationId): Notification
    {
        return $this->repository->markAsRead($storeId, $notificationId);
    }

    public function markAllAsRead(int $storeId): int
    {
        return $this->repository->markAllAsRead($storeId);
    }

    public function delete(int $storeId, int $notificationId): void
    {
        $this->repository->deleteForStore($storeId, $notificationId);
    }

}
