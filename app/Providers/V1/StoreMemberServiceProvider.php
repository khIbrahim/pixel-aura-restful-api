<?php

namespace App\Providers\V1;

use App\Contracts\V1\Export\StoreMemberExportServiceInterface;
use App\Services\V1\StoreMember\StoreMemberExportService;
use Illuminate\Support\ServiceProvider;

class StoreMemberServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(
            StoreMemberExportServiceInterface::class,
            StoreMemberExportService::class
        );
    }

}
