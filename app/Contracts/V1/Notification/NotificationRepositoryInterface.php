<?php

namespace App\Contracts\V1\Notification;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\Models\V1\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{

    public function list(int $storeId, bool $unread, int $perPage = 25): LengthAwarePaginator;

    public function markAsRead(int $storeId, int $notificationId): Notification;

    public function markAllAsRead(int $storeId): int;

    public function deleteForStore(int $storeId, int $notificationId): void;

}
