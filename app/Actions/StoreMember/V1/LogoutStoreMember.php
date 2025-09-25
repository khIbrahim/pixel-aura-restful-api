<?php

namespace App\Actions\StoreMember\V1;

use App\Contracts\V1\Auth\StoreMemberAuthServiceInterface;
use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use App\Support\Results\LogoutResult;

readonly class LogoutStoreMember
{

    public function __construct(
        private StoreMemberAuthServiceInterface $service
    ){}

    public function __invoke(StoreMember $storeMember, Device $device): LogoutResult
    {
        return $this->service->logout($storeMember, $device);
    }

}
