<?php

namespace App\Services\V1\StoreMember;

use App\Enum\StoreMemberRole;
use App\Models\V1\StoreMemberCounter;

class StoreMemberCodeService
{

    public function next(int $storeId, StoreMemberRole|string $role): int
    {
        if (is_string($role)) {
            $role = StoreMemberRole::from($role);
        }

        $counter = StoreMemberCounter::query()
            ->where('store_id', $storeId)
            ->where('role', $role->value)
            ->lockForUpdate()
            ->first();

        if(! $counter){
            $counter = StoreMemberCounter::create([
                'store_id'  => $storeId,
                'role'      => $role->value,
                'next_code' => 1
            ]);
        }

        $code = $counter->next_code;

        $counter->increment('next_code');
        $counter->save();

        return $code;
    }
}
