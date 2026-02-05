<?php

namespace App\Services\V1\Store;

use App\Models\V1\Store;
use App\Support\Registry\AbilityRegistry;

class StoreTokenService
{

    private const string TOKEN_PREFIX          = 'store_token';
    private const int    TOKEN_CACHE_TTL       = 3600;
    private const int    TOKEN_ROTATION_DAYS   = 30;

    public function __construct(
        private readonly AbilityRegistry $abilityRegistry
    ){}

    public function create(Store $store): array
    {
        $this->rotateOldTokens($store);

        $abilities = $this->abilityRegistry->getAllAbilities();
        $tokenName = $this->generateTokenName($store);

        return [];
    }

    private function rotateOldTokens(Store $store): void
    {
        $store->tokens()
            ->where('expires_at', '<=', now())
            ->delete();
    }

    private function generateTokenName()
    {

    }

}
