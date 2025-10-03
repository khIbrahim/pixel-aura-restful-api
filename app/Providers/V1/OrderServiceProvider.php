<?php

namespace App\Providers\V1;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Contracts\V1\Order\OrderServiceInterface;
use App\Repositories\V1\Order\OrderRepository;
use App\Services\V1\Order\OrderNumberService;
use App\Services\V1\Order\OrderService;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
