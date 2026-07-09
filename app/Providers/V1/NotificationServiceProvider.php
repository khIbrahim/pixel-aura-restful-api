<?php

namespace App\Providers\V1;

use App\Contracts\V1\Notification\NotificationRepositoryInterface;
use App\Contracts\V1\Notification\NotificationsServiceInterface;
use App\Repositories\V1\Notification\NotificationRepository;
use App\Services\V1\Notification\NotificationsService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

        $this->app->bind(
            NotificationsServiceInterface::class,
            fn($app) => new NotificationsService(app(NotificationRepositoryInterface::class))
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
