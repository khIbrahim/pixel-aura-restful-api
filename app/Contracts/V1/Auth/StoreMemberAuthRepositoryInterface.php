<?php

namespace App\Contracts\V1\Auth;

use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface StoreMemberAuthRepositoryInterface
{

    public function findByCode(int $storeId, string $code): ?StoreMember;


    public function findById(int $id): ?StoreMember;

    /**
     * Mettre à jour la date de dernière connexion et incrémenter le compteur de connexions
     */
    public function updateLoginStats(StoreMember $storeMember): bool;

    public function incrementFailedAttempts(StoreMember $storeMember): int;

    public function lockAccount(StoreMember $storeMember, Carbon $until): bool;

    public function resetFailedAttempts(StoreMember $storeMember): bool;

    public function getConnectedDevices(StoreMember $storeMember): Collection;

    public function isAttachedToDevice(StoreMember $storeMember, Device $device): bool;

    public function attachToDevice(StoreMember $storeMember, Device $device): bool;

    public function detachFromDevice(StoreMember $storeMember, Device $device): bool;

    public function detachFromAllDevices(StoreMember $storeMember): int;
}
