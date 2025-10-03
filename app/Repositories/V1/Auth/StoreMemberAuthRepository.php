<?php

namespace App\Repositories\V1\Auth;

use App\Contracts\V1\Auth\StoreMemberAuthRepositoryInterface;
use App\Enum\V1\StoreMemberRole;
use App\Exceptions\InvalidRolePrefixException;
use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreMemberAuthRepository implements StoreMemberAuthRepositoryInterface
{

    private const int CACHE_TTL = 600; // 10 minutes en sec

    public function findByCode(int $storeId, string $code): ?StoreMember
    {
        $cacheKey = sprintf("store_member:%d:code:%s", $storeId, $code);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use($storeId, $code) {
            $parts = explode("-", $code);
            if (count($parts) !== 2) {
                return null;
            }

            [$prefix, $number] = $parts;
            $number = (int) ltrim($number, '0');

            try {
                $role = StoreMemberRole::fromPrefix($prefix);
            } catch (InvalidRolePrefixException) {
                return null;
            }

            return StoreMember::query()
                ->where('store_id', $storeId)
                ->where('code_number', $number)
                ->where('is_active', true)
                ->where('role', 'LIKE', '%' . $role->value . '%')
                ->first();
        });
    }

    public function findById(int $id): ?StoreMember
    {
        $cacheKey = sprintf("store_member:id:%d", $id);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use($id) {
            return StoreMember::query()->find($id);
        });
    }

    public function updateLoginStats(StoreMember $storeMember): bool
    {
        $updated = $storeMember->update([
            'last_login_at' => now(),
            'login_count'   => DB::raw('login_count + 1'),
        ]);

        if ($updated){
            $this->invalidateCache($storeMember);
        }

        return $updated;
    }

    public function incrementFailedAttempts(StoreMember $storeMember): int
    {
        $storeMember->increment('failed_attempts');

        $this->invalidateCache($storeMember);

        return $storeMember->failed_attempts;
    }

    public function lockAccount(StoreMember $storeMember, Carbon $until): bool
    {
        $updated = $storeMember->update([
            'locked_until' => $until,
        ]);

        if ($updated){
            $this->invalidateCache($storeMember);
        }

        return $updated;
    }

    public function resetFailedAttempts(StoreMember $storeMember): bool
    {
        $updated = $storeMember->update([
            'failed_attempts' => 0,
            'locked_until'    => null,
        ]);

        if ($updated){
            $this->invalidateCache($storeMember);
        }

        return $updated;
    }

    public function getConnectedDevices(StoreMember $storeMember): Collection
    {
        return Device::query()
            ->whereHas('tokens', function ($query) use ($storeMember) {
                $query->where('store_member_id', $storeMember->id)
                    ->where('revoked', false);
            })
            ->get();
    }

    public function isAttachedToDevice(StoreMember $storeMember, Device $device): bool
    {
        return $device->tokens()
            ->where('store_member_id', $storeMember->id)
            ->where('revoked', false)
            ->exists();
    }

    public function attachToDevice(StoreMember $storeMember, Device $device): bool
    {
        return DB::transaction(function () use ($storeMember, $device){
            $device->tokens()
                ->where('store_member_id', '!=', $storeMember->id)
                ->update(['revoked' => true]);

            return $device->tokens()
                ->where('revoked', false)
                ->update(['store_member_id' => $storeMember->id]);
        });
    }

    public function detachFromDevice(StoreMember $storeMember, Device $device): bool
    {
        return $device->tokens()
            ->where('store_member_id', $storeMember->id)
            ->update(['store_member_id' => null]);
    }

    public function detachFromAllDevices(StoreMember $storeMember): int
    {
        return DB::table('personal_access_tokens')
            ->where('store_member_id', $storeMember->id)
            ->update(['store_member_id' => null]);
    }

    private function invalidateCache(StoreMember $storeMember): void
    {
        $cacheKeyById   = sprintf("store_member:id:%d", $storeMember->id);
        $cacheKeyByCode = sprintf("store_member:%d:code:%s", $storeMember->id, $storeMember->code());

        Cache::forget($cacheKeyById);
        Cache::forget($cacheKeyByCode);
    }
}
