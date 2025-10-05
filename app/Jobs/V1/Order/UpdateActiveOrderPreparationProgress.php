<?php

namespace App\Jobs\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Enum\V1\Order\OrderStatus;
use App\Services\V1\Order\OrderPreparationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateActiveOrderPreparationProgress implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly OrderPreparationService  $service
    ){}

    public function handle(): void
    {
        $activeStatuses = [
            OrderStatus::Completed,
            OrderStatus::Preparing
        ];

        $orders = $this->repository->getOrdersByStatuses($activeStatuses);
        foreach($orders as $order){
            $this->service->updatePreparationProgress($order);
        }
    }
}
