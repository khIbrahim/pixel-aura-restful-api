<?php

namespace App\Contracts\V1\Auth;

use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use App\Support\Results\AuthenticateResult;
use App\Support\Results\LogoutResult;

interface StoreMemberAuthServiceInterface
{

    public function authenticate(int $storeId, string $code, string $pin, Device $device): AuthenticateResult;

    public function logout(StoreMember $storeMember, Device $device): LogoutResult;

    public function logoutFromAllDevices(StoreMember $storeMember): LogoutResult;

    public function hasPermission(StoreMember $storeMember, string $ability): bool;

    public function getConnectedDevices(StoreMember $storeMember): array;

    public function getEnrichedMemberData(StoreMember $storeMember): array;
}
