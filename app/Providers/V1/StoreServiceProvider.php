<?php

namespace App\Providers\V1;

use App\Contracts\V1\Store\StoreRepositoryInterface;
use App\Repositories\V1\Store\StoreRepository;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
