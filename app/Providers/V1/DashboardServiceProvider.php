<?php

namespace App\Providers\V1;

use App\Contracts\V1\Dashboard\DashboardStatsRepositoryInterface;
use App\Contracts\V1\Dashboard\DashboardStatsServiceInterface;
use App\Repositories\V1\Dashboard\DashboardStatsRepository;
use App\Services\V1\Dashboard\DashboardStatsService;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardStatsRepositoryInterface::class, DashboardStatsRepository::class);
        $this->app->bind(DashboardStatsServiceInterface::class, DashboardStatsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
