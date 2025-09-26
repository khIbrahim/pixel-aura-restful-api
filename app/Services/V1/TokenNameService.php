<?php

namespace App\Services\V1;

use App\Models\V1\Store;
use App\Models\V1\StoreMember;

class TokenNameService
{

    const POS             = 'pos';
    const OWNER_PREFIX    = 'owner';
    const EMPLOYEE_PREFIX = 'employee';

    /**
     * @param Store $store
     * @return string
     */
    public static function forOwner(Store $store): string
    {
        return implode("-", [
            self::OWNER_PREFIX,
            $store->sku,
            now()->timestamp
        ]);
    }

    public static function forStoreMember(StoreMember $storeMember): string
    {
        return implode("-", [
            self::EMPLOYEE_PREFIX,
            $storeMember->name,
            $storeMember->role->value,
            now()->timestamp
        ]);
    }

    public static function forApiIntegration(Store $store, string $integration): string
    {
        return implode("-", [
            $store->sku,
            $integration,
            now()->timestamp
        ]);
    }

    //TODO PARSE

}
