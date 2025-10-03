<?php

namespace App\Contracts\V1\Order;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\DTO\V1\Order\OrderData;
use App\Models\V1\Order;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{

    public function createOrder(OrderData $data): Order;

}
