<?php

namespace App\Observers\V1;

use App\Contracts\V1\Order\OrderServiceInterface;
use App\Enum\V1\Order\OrderStatus;
use App\Models\V1\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver extends BaseObserver
{

    public function __construct(
        private readonly OrderServiceInterface $orderService
    ){}

    public function created(Order $order): void
    {
        if ($order->status && $order->status === OrderStatus::Confirmed) {
            $this->orderService->updateEstimatedTime($order);
        }
    }

    public function updated(Order $order): void
    {
        $newStatus = $order->status->value;

        if (in_array($newStatus, [OrderStatus::Confirmed, OrderStatus::Preparing])) {
            $this->orderService->updateEstimatedTime($order);
        }
    }

}
