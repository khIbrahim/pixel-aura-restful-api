<?php

namespace App\Contracts\V1\Notification;

use App\Models\V1\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationsServiceInterface
{

    public function list(int $storeId, bool $unread, int $perPage = 25): LengthAwarePaginator;

    public function markAsRead(int $storeId, int $notificationId): Notification;

    public function markAllAsRead(int $storeId): int;

    public function delete(int $storeId, int $notificationId): void;

}
