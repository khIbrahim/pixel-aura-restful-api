<?php

namespace App\Contracts\V1\Order;

use App\Enum\V1\Order\OrderStatus;
use App\Models\V1\Order;

interface OrderStatusServiceInterface
{

    public function transition(int $storeId, Order $order, OrderStatus $newStatus): Order;

}
