<?php

namespace App\Providers\V1;

use App\Contracts\V1\Auth\StoreMemberAuthRepositoryInterface;
use App\Contracts\V1\Auth\StoreMemberAuthServiceInterface;
use App\Http\Middleware\AuthenticateStoreMemberMiddleware;
use App\Repositories\V1\Auth\StoreMemberAuthRepository;
use App\Services\V1\Auth\StoreMemberAuthService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StoreMemberAuthServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(
            StoreMemberAuthRepositoryInterface::class,
            StoreMemberAuthRepository::class
        );

        $this->app->bind(
            StoreMemberAuthServiceInterface::class,
            StoreMemberAuthService::class
        );
    }

    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('auth.store_member', AuthenticateStoreMemberMiddleware::class);
    }
}
