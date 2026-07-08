<?php

namespace App\Providers\V1;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Contracts\V1\Order\OrderServiceInterface;
use App\Contracts\V1\Order\OrderStatusServiceInterface;
use App\Models\V1\Order;
use App\Observers\V1\OrderObserver;
use App\Repositories\V1\Order\OrderRepository;
use App\Services\V1\Order\OrderNumberService;
use App\Services\V1\Order\OrderPreparationService;
use App\Services\V1\Order\OrderService;
use App\Services\V1\Order\OrderStatusService;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
        $this->app->singleton(OrderNumberService::class);
        $this->app->bind(
            OrderServiceInterface::class,
            OrderService::class
        );
        $this->app->bind(
            OrderPreparationService::class,
            fn($app) => new OrderPreparationService(config('pos.order.preparation_time', []), app(OrderRepositoryInterface::class))
        );

        $this->app->bind(
            OrderStatusServiceInterface::class,
            OrderStatusService::class
        );
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
    }
}
