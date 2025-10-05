<?php

namespace App\Contracts\V1\Order;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\DTO\V1\Order\OrderData;
use App\Models\V1\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{

    public function createOrder(OrderData $data): Order;

    public function countActiveOrdersInLastMinutes(int $storeId, ?int $minutes = 10): int;

    public function getOrdersByStatuses(array $statuses): Collection;

}
