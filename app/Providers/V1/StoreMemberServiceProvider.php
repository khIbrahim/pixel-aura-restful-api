<?php

namespace App\Providers\V1;

use App\Contracts\V1\Export\StoreMemberExportServiceInterface;
use App\Contracts\V1\StoreMember\StoreMemberRepositoryInterface;
use App\Contracts\V1\StoreMember\StoreMemberServiceInterface;
use App\Repositories\V1\StoreMember\StoreMemberRepository;
use App\Services\V1\StoreMember\StoreMemberExportService;
use App\Services\V1\StoreMember\StoreMemberService;
use Illuminate\Support\ServiceProvider;

class StoreMemberServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(StoreMemberRepositoryInterface::class, StoreMemberRepository::class);
        $this->app->bind(StoreMemberServiceInterface::class, StoreMemberService::class);

        $this->app->bind(
            StoreMemberExportServiceInterface::class,
            StoreMemberExportService::class
        );
    }

}
