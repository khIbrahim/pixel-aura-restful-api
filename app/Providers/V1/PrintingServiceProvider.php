<?php

namespace App\Providers\V1;

use App\Contracts\V1\Printing\PrintGatewayInterface;
use App\Contracts\V1\Printing\PrintingServiceInterface;
use App\Contracts\V1\Printing\StorePrintSettingsRepositoryInterface;
use App\Repositories\V1\Printing\StorePrintSettingsRepository;
use App\Services\V1\Printing\GoPrintGateway;
use App\Services\V1\Printing\PrintingService;
use Illuminate\Support\ServiceProvider;

class PrintingServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(
            StorePrintSettingsRepositoryInterface::class,
            StorePrintSettingsRepository::class
        );

        $this->app->bind(
            PrintGatewayInterface::class,
            GoPrintGateway::class
        );

        $this->app->bind(
            PrintingServiceInterface::class,
            PrintingService::class
        );
    }
}
