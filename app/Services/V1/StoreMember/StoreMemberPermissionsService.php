<?php

namespace App\Services\V1\StoreMember;

use App\Constants\V1\StoreTokenAbilities;
use App\Enum\V1\StoreMemberRole;

final class StoreMemberPermissionsService
{

    public function getByRole(StoreMemberRole|string $role): array
    {
        if(is_string($role)){
            $role = StoreMemberRole::tryFrom($role);
        }

        if (! $role) {
            return [];
        }

        return StoreTokenAbilities::getAbilitiesByRole($role);
    }

}
