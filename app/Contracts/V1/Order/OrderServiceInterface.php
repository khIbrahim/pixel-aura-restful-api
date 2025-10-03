<?php

namespace App\Contracts\V1\Order;

use App\DTO\V1\Order\OrderData;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Models\V1\Order;

interface OrderServiceInterface
{

    /**
     * @throws OrderCreationException
     */
    public function create(OrderData $data): Order;

}
