<?php

namespace App\Actions\StoreMember\V1;

use App\Contracts\V1\Auth\StoreMemberAuthServiceInterface;
use App\DTO\V1\StoreMember\AuthenticateStoreMemberDTO;
use App\Models\V1\Device;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Support\Results\AuthenticateResult;

readonly class AuthenticateStoreMember
{

    public function __construct(private StoreMemberAuthServiceInterface $storeMemberService){}

    public function __invoke(Store $store, StoreMember $storeMember, AuthenticateStoreMemberDTO $data, Device $device): AuthenticateResult
    {
        return $this->storeMemberService->authenticate($store->id, $data->code, $data->pin, $device);
    }

}
