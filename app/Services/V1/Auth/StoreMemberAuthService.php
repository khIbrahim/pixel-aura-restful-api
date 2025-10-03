<?php

namespace App\Services\V1\Auth;

use App\Contracts\V1\Auth\StoreMemberAuthRepositoryInterface;
use App\Contracts\V1\Auth\StoreMemberAuthServiceInterface;
use App\Events\V1\Auth\StoreMemberAuthenticated;
use App\Events\V1\Auth\StoreMemberLoggedOut;
use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use App\Support\Results\AuthenticateResult;
use App\Support\Results\LogoutResult;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class StoreMemberAuthService implements StoreMemberAuthServiceInterface
{

    private const int MAX_ATTEMPTS = 10;

    private const int LOCKOUT_DURATION = 15;

    public function __construct(
        private readonly StoreMemberAuthRepositoryInterface $repository,
        private readonly Cache                              $cache,
        private readonly AbilityManager                     $abilityManager
    ) {
    }

    public function authenticate(int $storeId, string $code, string $pin, Device $device): AuthenticateResult
    {
        $key = sprintf("auth_attempts:%d", $device->id);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning("Trop de tentatives d'authentification pour l'appareil $device->id", [
                'store_id'          => $storeId,
                'device_id'         => $device->id,
                'seconds_remaining' => $seconds
            ]);

            return AuthenticateResult::rateLimitExceeded($seconds);
        }

        $storeMember = $this->repository->findByCode($storeId, $code);

        if (! $storeMember) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

            Log::info("Store member non trouvé", [
                'store_id'  => $storeId,
                'code'      => $code,
                'device_id' => $device->id
            ]);

            return AuthenticateResult::invalidCredentials('Identifiants (code, id) invalides');
        }

        if ($storeMember->locked_until && $storeMember->locked_until > now()) {
            $secondsRemaining = now()->diffInSeconds($storeMember->locked_until);

            Log::info("Store member vérouillé", [
                'store_id'     => $storeId,
                'member_id'    => $storeMember->id,
                'locked_until' => $storeMember->locked_until
            ]);

            return AuthenticateResult::accountLocked($secondsRemaining);
        }

        if (! $storeMember->checkPin($pin)) {
            $failedAttempts = $this->repository->incrementFailedAttempts($storeMember);

            if ($failedAttempts >= self::MAX_ATTEMPTS) {
                $lockUntil = now()->addMinutes(self::LOCKOUT_DURATION);
                $this->repository->lockAccount($storeMember, $lockUntil);

                Log::warning("Compte vérouillé, trop de tentative d'authentification échouées", [
                    'store_id'     => $storeId,
                    'member_id'    => $storeMember->id,
                    'locked_until' => $lockUntil
                ]);

                return AuthenticateResult::accountLocked(self::LOCKOUT_DURATION * 60);
            }

            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

            Log::info("Authentification échouée, PIN Invalide", [
                'store_id'        => $storeId,
                'member_id'       => $storeMember->id,
                'failed_attempts' => $failedAttempts
            ]);

            return AuthenticateResult::invalidCredentials('PIN invalide');
        }

        $this->repository->resetFailedAttempts($storeMember);
        $this->repository->updateLoginStats($storeMember);
        $this->abilityManager->warmMemberCache($storeMember);

        if ($this->repository->isAttachedToDevice($storeMember, $device)){
            Log::info("Store member déjà connecté à l'appareil", [
                'store_id'  => $storeId,
                'member_id' => $storeMember->id,
                'device_id' => $device->id
            ]);

            return AuthenticateResult::alreadyAuthenticated($storeMember);
        }

        $this->repository->attachToDevice($storeMember, $device);

        RateLimiter::clear($key);

        broadcast(new StoreMemberAuthenticated($storeMember, $device))->toOthers();

        Log::info("Authentification réussie", [
            'store_id'  => $storeId,
            'member_id' => $storeMember->id,
            'device_id' => $device->id
        ]);

        return AuthenticateResult::successfulAuthentication($storeMember);
    }

    public function logout(StoreMember $storeMember, Device $device): LogoutResult
    {
        $success = $this->repository->detachFromDevice($storeMember, $device);

        if ($success) {
            $this->abilityManager->clearMemberCache($storeMember);
            broadcast(new StoreMemberLoggedOut($storeMember, $device))->toOthers();

            Log::info("Store member déconnecté de l'appareil", [
                'member_id' => $storeMember->id,
                'device_id' => $device->id
            ]);

            return LogoutResult::success('Déconnexion réussie');
        }

        Log::warning("Une erreur s'est produite lors de la déconnexion", [
            'member_id' => $storeMember->id,
            'device_id' => $device->id
        ]);

        return LogoutResult::failure("Échec de la déconnexion");
    }

    public function logoutFromAllDevices(StoreMember $storeMember): LogoutResult
    {
        $devices = $this->repository->getConnectedDevices($storeMember);
        $count   = $this->repository->detachFromAllDevices($storeMember);

        if ($count > 0) {
            foreach ($devices as $device) {
                broadcast(new StoreMemberLoggedOut($storeMember, $device))->toOthers();
            }

            Log::info("Store member déconnecté de tout les appareils", [
                'member_id'    => $storeMember->id,
                'device_count' => $count
            ]);

            return LogoutResult::success('Déconnexion de tout les appareils réussie');
        }

        Log::info("Store member connecté à aucun appareil", [
            'member_id' => $storeMember->id
        ]);

        return LogoutResult::success("Déconnexion de tout les appareils réussie");
    }

    public function hasPermission(StoreMember $storeMember, string $ability): bool
    {
        $cacheKey = sprintf("store_member:%d:permission:%s", $storeMember->id, $ability);

        return $this->cache->remember($cacheKey, 300, function () use ($storeMember, $ability) {
            return $storeMember->hasPermission($ability);
        });
    }

    public function getConnectedDevices(StoreMember $storeMember): array
    {
        $cacheKey = sprintf("store_member%d:connected_devices", $storeMember->id);

        return $this->cache->remember($cacheKey, 60, function () use ($storeMember) {
            $devices = $this->repository->getConnectedDevices($storeMember);

            return $devices->map(function ($device) {
                return [
                    'id'            => $device->id,
                    'name'          => $device->name,
                    'type'          => $device->type,
                    'last_activity' => $device->last_activity_at?->diffForHumans(),
                    'ip_address'    => $device->last_ip_address,
                ];
            })->toArray();
        });
    }

    public function getEnrichedMemberData(StoreMember $storeMember): array
    {
        return [
            'id'          => $storeMember->id,
            'store_id'    => $storeMember->store_id,
            'name'        => $storeMember->name,
            'code'        => $storeMember->code(),
            'role'        => $storeMember->role->value,
            'permissions' => $storeMember->getPermissions(),
        ];
    }
}
