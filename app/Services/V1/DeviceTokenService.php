<?php

namespace App\Services\V1;

use App\Enum\DeviceType;
use App\Models\V1\Device;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\NewAccessToken;
use Throwable;

class DeviceTokenService
{

    private const string TOKEN_PREFIX       = 'device_token';
    private const int TOKEN_CACHE_TTL       = 3600;
    private const int MAX_TOKENS_PER_DEVICE = 3;
    private const int TOKEN_ROTATION_DAYS   = 30;

    public function createDeviceToken(Store $store, DeviceType $deviceType, string $fingerprint, ?string $deviceName = null, ?array $abilities = null): array
    {
        $device = $this->createOrUpdateDevice($store, $deviceType, $fingerprint, $deviceName);

        $this->rotateOldTokens($device);

        $defaultAbilities = $deviceType->getAbilities();
        $finalAbilities   = array_unique(array_merge($defaultAbilities, $abilities ?? []));

        $tokenName = $this->generateTokenName($device);
        $token     = $device->createToken($tokenName, $finalAbilities, now()->addDays(self::TOKEN_ROTATION_DAYS));
        $token->accessToken->forceFill([
            'store_id'        => $store->id,
            'device_id'       => $device->id,
            'fingerprint'     => $fingerprint,
        ])->save();

        $this->cacheTokenInfo($token, $device);

        $this->logTokenCreation($token, $device);

        return [
            'token'            => $token->plainTextToken,
            'token_name'       => $token->accessToken->name,
            'device_id'        => $device->id,
            'expires_at'       => $token->accessToken->expires_at,
            'abilities'        => $finalAbilities,
            'fingerprint_hash' => $device->fingerprint_hash,
            'fingerprint'      => $device->fingerprint
        ];
    }

    private function createOrUpdateDevice(Store $store, DeviceType $deviceType, string $fingerprint, ?string $deviceName): Device
    {
        $fingerprintHash = hash('sha256', $fingerprint);

        return Device::updateOrCreate(
            [
                'store_id'         => $store->id,
                'fingerprint_hash' => $fingerprintHash,
                'type'             => $deviceType->value
            ],
            [
                'name'         => $deviceName ?? $this->generateDeviceName($store, $deviceType),
                'fingerprint'  => $fingerprint,
                'is_blocked'   => false,
                'last_seen_at' => now(),
                'device_info'  => $this->gatherDeviceInfo()
            ]
        );
    }

    private function generateDeviceName(Store $store, DeviceType $deviceType): string
    {
        try {
            $count = $store->devices()->where('type', $deviceType->value)->count();
        } catch (Throwable){
            $count = 0;
        }

        return sprintf('%s #%d', $deviceType->getDisplayName(), $count + 1);
    }

    private function gatherDeviceInfo(): array
    {
        return [
            'created_at' => now(),
            'user_agent' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
            'platform'   => $this->detectPlatform(),
        ];
    }

    private function detectPlatform(): string
    {
        $userAgent = request()->header('User-Agent', '');

        if (str_contains($userAgent, 'Android')) return 'android';
        if (str_contains($userAgent, 'iOS')) return 'ios';
        if (str_contains($userAgent, 'Windows')) return 'windows';
        if (str_contains($userAgent, 'Mac')) return 'mac';
        if (str_contains($userAgent, 'Linux')) return 'linux';

        return 'unknown';
    }

    private function rotateOldTokens(Device $device): void
    {
        $tokenCount = $device->tokens()->count();

        if ($tokenCount >= self::MAX_TOKENS_PER_DEVICE){
            $device->tokens()
                ->oldest()
                ->take($tokenCount - self::MAX_TOKENS_PER_DEVICE + 1)
                ->get()
                ->each(fn($token) => $token->delete());
        }

        $device->tokens()
            ->where('expires_at', '<', now())
            ->delete();
    }

    private function generateTokenName(Device $device): string
    {
        return sprintf(
            '%s:%s:%s:%s',
            self::TOKEN_PREFIX,
            $device->store->sku,
            $device->type->value,
            $device->id
        );
    }

    private function cacheTokenInfo(NewAccessToken $token, Device $device): void
    {
        $cacheKey = 'device_token:' . hash('sha256', $token->plainTextToken);

        Cache::put($cacheKey, [
            'device_id'   => $device->id,
            'store_id'    => $device->store_id,
            'fingerprint' => $device->fingerprint_hash,
            'abilities'   => $token->accessToken->abilities,
        ], self::TOKEN_CACHE_TTL);
    }

    private function logTokenCreation(NewAccessToken $token, Device $device): void
    {
        activity()
            ->performedOn($device)
            ->withProperties([
                'token_id'   => $token->accessToken->id,
                'abilities'  => $token->accessToken->abilities,
                'expires_at' => $token->accessToken->expires_at,
                'ip_address' => request()->ip(),
            ])
            ->log('device_token_created');
    }

}
