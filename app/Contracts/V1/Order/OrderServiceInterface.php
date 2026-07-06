<?php

namespace App\Contracts\V1\Order;

use App\DTO\V1\Order\OrderData;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Models\V1\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{

    /**
     * @throws OrderCreationException
     */
    public function create(OrderData $data): Order;

    public function updateEstimatedTime(Order $order): Order;

    public function listForDashboard(int $storeId, array $filters, int $perPage = 25, string $timezone = 'UTC'): LengthAwarePaginator;

}
